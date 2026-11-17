<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Agenda da unidade ativa.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $context = $this->activeContext($request);

        if (! $user->isVitallisAdmin() && ! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho para acessar a agenda.'
            );
        }

        return view('appointments.index', [
            'user' => $user,
            'context' => $context,
        ]);
    }

    /**
     * Formulário para novo agendamento.
     */
    public function create(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de criar um agendamento.'
            );
        }

        /*
         * Pacientes disponíveis na rede da unidade atual.
         */
        $patients = Patient::query()
            ->where('active', true)
            ->whereHas(
                'networks',
                function ($query) use ($context) {
                    $query
                        ->where(
                            'networks.id',
                            $context->network_id
                        )
                        ->where(
                            'patient_network.active',
                            true
                        );
                }
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'social_name',
                'cpf',
                'birth_date',
            ]);

        /*
         * Somente grades ativas da unidade atual.
         */
        $schedules = Schedule::query()
            ->with('professional')
            ->where(
                'network_id',
                $context->network_id
            )
            ->where(
                'unit_id',
                $context->unit_id
            )
            ->where('active', true)
            ->orderBy('professional_user_id')
            ->orderBy('weekday')
            ->orderBy('starts_at')
            ->get();

        $unit = Unit::findOrFail(
            $context->unit_id
        );

        return view(
            'appointments.create',
            compact(
                'patients',
                'schedules',
                'unit',
                'context'
            )
        );
    }

    /**
     * Salva um novo agendamento.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de criar um agendamento.'
            );
        }

        $validated = $request->validate([
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,id',
            ],

            'schedule_id' => [
                'required',
                'integer',
                'exists:schedules,id',
            ],

            'date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'time' => [
                'required',
                'date_format:H:i',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'administrative_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        /*
         * Confirma que o paciente pertence à rede atual.
         */
        $patient = Patient::query()
            ->whereKey($validated['patient_id'])
            ->where('active', true)
            ->whereHas(
                'networks',
                function ($query) use ($context) {
                    $query
                        ->where(
                            'networks.id',
                            $context->network_id
                        )
                        ->where(
                            'patient_network.active',
                            true
                        );
                }
            )
            ->first();

        if (! $patient) {
            abort(
                403,
                'O paciente não está disponível na rede atual.'
            );
        }

        /*
         * Confirma que a grade pertence à unidade ativa.
         */
        $schedule = Schedule::query()
            ->whereKey($validated['schedule_id'])
            ->where(
                'network_id',
                $context->network_id
            )
            ->where(
                'unit_id',
                $context->unit_id
            )
            ->where('active', true)
            ->first();

        if (! $schedule) {
            abort(
                403,
                'A agenda selecionada não pertence à unidade atual.'
            );
        }

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $validated['date']
        )->startOfDay();

        /*
         * O dia selecionado precisa corresponder à grade.
         * Carbon: 0 domingo ... 6 sábado.
         */
        if ($date->dayOfWeek !== (int) $schedule->weekday) {
            throw ValidationException::withMessages([
                'date' =>
                    'A agenda selecionada não possui atendimento neste dia da semana.',
            ]);
        }

        if (
            $date->lt($schedule->starts_on->startOfDay())
            || (
                $schedule->ends_on
                && $date->gt(
                    $schedule->ends_on->startOfDay()
                )
            )
        ) {
            throw ValidationException::withMessages([
                'date' =>
                    'A data escolhida está fora do período de validade desta agenda.',
            ]);
        }

        $startsAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['date'].' '.$validated['time']
        );

        $scheduleStartsAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $validated['date'].' '.$schedule->starts_at
        );

        $scheduleEndsAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $validated['date'].' '.$schedule->ends_at
        );

        $endsAt = $startsAt
            ->copy()
            ->addMinutes(
                $schedule->slot_duration_minutes
            );

        /*
         * O horário precisa caber integralmente na grade.
         */
        if (
            $startsAt->lt($scheduleStartsAt)
            || $endsAt->gt($scheduleEndsAt)
        ) {
            throw ValidationException::withMessages([
                'time' =>
                    'O horário informado está fora da grade selecionada.',
            ]);
        }

        /*
         * O horário também precisa respeitar o intervalo
         * configurado na grade.
         */
        $minutesFromScheduleStart =
            $scheduleStartsAt->diffInMinutes(
                $startsAt,
                false
            );

        if (
            $minutesFromScheduleStart < 0
            || $minutesFromScheduleStart
                % $schedule->slot_duration_minutes !== 0
        ) {
            throw ValidationException::withMessages([
                'time' =>
                    'O horário informado não corresponde a um horário válido desta agenda.',
            ]);
        }

        DB::transaction(
            function () use (
                $validated,
                $context,
                $schedule,
                $patient,
                $user,
                $startsAt,
                $endsAt
            ) {
                /*
                 * Bloqueia a grade durante a verificação
                 * para reduzir risco de dupla marcação.
                 */
                Schedule::query()
                    ->whereKey($schedule->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $appointmentsAtSlot = Appointment::query()
                    ->where(
                        'schedule_id',
                        $schedule->id
                    )
                    ->where(
                        'starts_at',
                        $startsAt
                    )
                    ->whereNotIn(
                        'status',
                        [
                            'cancelled',
                            'no_show',
                        ]
                    )
                    ->count();

                if (
                    $appointmentsAtSlot
                    >= $schedule->slot_capacity
                ) {
                    throw ValidationException::withMessages([
                        'time' =>
                            'Este horário não possui mais vagas disponíveis.',
                    ]);
                }

                /*
                 * Não permite duplicar o mesmo paciente
                 * no mesmo horário da mesma agenda.
                 */
                $patientAlreadyScheduled =
                    Appointment::query()
                        ->where(
                            'schedule_id',
                            $schedule->id
                        )
                        ->where(
                            'patient_id',
                            $patient->id
                        )
                        ->where(
                            'starts_at',
                            $startsAt
                        )
                        ->whereNotIn(
                            'status',
                            [
                                'cancelled',
                                'no_show',
                            ]
                        )
                        ->exists();

                if ($patientAlreadyScheduled) {
                    throw ValidationException::withMessages([
                        'patient_id' =>
                            'Este paciente já possui agendamento neste horário.',
                    ]);
                }

                Appointment::create([
                    'network_id' =>
                        $context->network_id,

                    'unit_id' =>
                        $context->unit_id,

                    'schedule_id' =>
                        $schedule->id,

                    'patient_id' =>
                        $patient->id,

                    'professional_user_id' =>
                        $schedule->professional_user_id,

                    'starts_at' =>
                        $startsAt,

                    'ends_at' =>
                        $endsAt,

                    'status' =>
                        'scheduled',

                    'reason' =>
                        $validated['reason'] ?? null,

                    'administrative_notes' =>
                        $validated['administrative_notes']
                        ?? null,

                    'created_by_user_id' =>
                        $user->id,
                ]);
            }
        );

        return redirect()
            ->route('appointments.index')
            ->with(
                'success',
                'Agendamento criado com sucesso.'
            );
    }

    /**
     * Resolve o contexto operacional ativo.
     */
    private function activeContext(
        Request $request
    ): ?object {
        $user = $request->user();

        /*
         * Primeiro tenta a sessão.
         */
        $networkId = $request->session()->get(
            'active_network_id'
        );

        $unitId = $request->session()->get(
            'active_unit_id'
        );

        if ($networkId && $unitId) {
            return (object) [
                'network_id' => (int) $networkId,
                'unit_id' => (int) $unitId,
            ];
        }

        /*
         * Fallback para user_contexts.
         */
        $context = DB::table('user_contexts')
            ->where(
                'user_id',
                $user->id
            )
            ->first();

        if (
            ! $context
            || ! $context->network_id
            || ! $context->unit_id
        ) {
            return null;
        }

        return (object) [
            'network_id' =>
                (int) $context->network_id,

            'unit_id' =>
                (int) $context->unit_id,
        ];
    }
}