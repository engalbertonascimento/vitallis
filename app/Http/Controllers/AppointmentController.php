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
use App\Models\Specialty;
use App\Models\User;
use App\Models\MedicalEncounter;

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
        abort(403, 'Selecione uma unidade de trabalho para acessar a agenda.');
    }

    if (! $context) {
        abort(403, 'Selecione uma unidade de trabalho para acessar a agenda.');
    }

    // Data selecionada
    try {
        $date = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay()
            : now()->startOfDay();
    } catch (\Throwable $e) {
        $date = now()->startOfDay();
    }

    // Grades válidas para o dia selecionado
    $schedulesQuery = Schedule::query()
        ->with([
            'professional',
            'specialtyRelation',
            'serviceLocation',
        ])
        ->where('network_id', $context->network_id)
        ->where('unit_id', $context->unit_id)
        ->where('active', true)
        ->where('weekday', $date->dayOfWeek)
        ->whereDate('starts_on', '<=', $date->toDateString())
        ->where(function ($query) use ($date) {
            $query
                ->whereNull('ends_on')
                ->orWhereDate('ends_on', '>=', $date->toDateString());
        });

    // Filtro profissional
    if ($request->filled('professional_id')) {
        $schedulesQuery->where(
            'professional_user_id',
            $request->integer('professional_id')
        );
    }

    // Filtro especialidade
    if ($request->filled('specialty_id')) {
        $schedulesQuery->where(
            'specialty_id',
            $request->integer('specialty_id')
        );
    }

    $schedules = $schedulesQuery
        ->orderBy('starts_at')
        ->get();

    // Profissionais disponíveis na unidade
    $professionals = User::query()
        ->whereIn(
            'id',
            Schedule::query()
                ->where('network_id', $context->network_id)
                ->where('unit_id', $context->unit_id)
                ->where('active', true)
                ->whereNotNull('professional_user_id')
                ->select('professional_user_id')
        )
        ->orderBy('name')
        ->get();

    // Especialidades disponíveis na unidade
    $specialties = Specialty::query()
        ->where('active', true)
        ->whereIn(
            'id',
            Schedule::query()
                ->where('network_id', $context->network_id)
                ->where('unit_id', $context->unit_id)
                ->where('active', true)
                ->whereNotNull('specialty_id')
                ->select('specialty_id')
        )
        ->orderBy('name')
        ->get();

    // Agendamentos do dia
    $appointmentsQuery = Appointment::query()
        ->with([
            'patient',
            'professional',
            'schedule.specialtyRelation',
        ])
        ->where('network_id', $context->network_id)
        ->where('unit_id', $context->unit_id)
        ->whereDate('starts_at', $date->toDateString());

    if ($request->filled('professional_id')) {
        $appointmentsQuery->where(
            'professional_user_id',
            $request->integer('professional_id')
        );
    }

    if ($request->filled('specialty_id')) {
        $appointmentsQuery->whereHas(
            'schedule',
            fn ($query) => $query->where(
                'specialty_id',
                $request->integer('specialty_id')
            )
        );
    }

    if ($request->filled('status')) {
        $appointmentsQuery->where('status', $request->status);
    }

    $appointments = $appointmentsQuery
        ->orderBy('starts_at')
        ->get();

    return view('appointments.index', [
        'user' => $user,
        'context' => $context,
        'date' => $date,
        'schedules' => $schedules,
        'appointments' => $appointments,
        'professionals' => $professionals,
        'specialties' => $specialties,
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
    /**
 * Registra a chegada do paciente.
 */
public function checkin(
    Request $request,
    Appointment $appointment
): RedirectResponse {

    $context = $this->activeContext($request);

    if (
        ! $context
        || $appointment->network_id !== $context->network_id
        || $appointment->unit_id !== $context->unit_id
    ) {
        abort(403, 'Este agendamento não pertence à unidade atual.');
    }

    if (! in_array($appointment->status, ['scheduled', 'confirmed'])) {
        return back()->with(
            'error',
            'Este atendimento não permite check-in.'
        );
    }

    $appointment->update([
        'status' => 'waiting',
        'checked_in_at' => now(),
    ]);

    return back()->with(
        'success',
        'Chegada do paciente registrada com sucesso.'
    );
}
/**
 * Registra ausência do paciente.
 */
public function noShow(
    Request $request,
    Appointment $appointment
): RedirectResponse {

    $context = $this->activeContext($request);

    if (
        ! $context
        || $appointment->network_id !== $context->network_id
        || $appointment->unit_id !== $context->unit_id
    ) {
        abort(403, 'Este agendamento não pertence à unidade atual.');
    }

    if (! in_array($appointment->status, ['scheduled', 'confirmed'])) {
        return back()->with(
            'error',
            'Este atendimento não permite registrar ausência.'
        );
    }

    if (now()->lt($appointment->starts_at)) {
        return back()->with(
            'error',
            'Não é possível registrar ausência antes do horário agendado.'
        );
    }

    $appointment->update([
        'status' => 'no_show',
    ]);

    return back()->with(
        'success',
        'Paciente registrado como não compareceu.'
    );
}

/**
 * Inicia o atendimento e cria o episódio clínico.
 */
public function start(
    Request $request,
    Appointment $appointment
): RedirectResponse {
    $user = $request->user();

    $context = $this->activeContext($request);

    if (
        ! $context
        || (int) $appointment->network_id
            !== (int) $context->network_id
        || (int) $appointment->unit_id
            !== (int) $context->unit_id
    ) {
        abort(
            403,
            'Este agendamento não pertence à unidade atual.'
        );
    }

    /*
     * O atendimento deve ser iniciado pelo profissional
     * responsável ou por alguém com supervisão clínica.
     */
    $isResponsibleProfessional =
        (int) $appointment->professional_user_id
        === (int) $user->id;

    $canSupervise = $user->hasPermission(
        'medical_records.supervise'
    );

    if (
        ! $isResponsibleProfessional
        && ! $canSupervise
        && ! $user->isVitallisAdmin()
    ) {
        abort(
            403,
            'Somente o profissional responsável ou um supervisor autorizado pode iniciar este atendimento.'
        );
    }

    if ($appointment->status !== 'waiting') {
        return back()->with(
            'error',
            'Somente pacientes aguardando podem iniciar atendimento.'
        );
    }

    DB::transaction(
        function () use (
            $appointment,
            $user,
            $context
        ) {
            /*
             * Bloqueia o agendamento para impedir
             * dois profissionais iniciando simultaneamente.
             */
            $lockedAppointment = Appointment::query()
                ->with([
                    'schedule',
                    'medicalEncounter',
                ])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAppointment->status !== 'waiting') {
                throw ValidationException::withMessages([
                    'appointment' =>
                        'Este atendimento já foi iniciado ou não está mais aguardando.',
                ]);
            }

            $startedAt = now();

            $lockedAppointment->update([
                'status' => 'in_progress',
            ]);

            /*
             * Um agendamento gera somente um episódio clínico.
             *
             * firstOrCreate também protege casos em que uma
             * tentativa anterior criou o episódio antes de
             * uma nova requisição ou repetição do fluxo.
             */
            MedicalEncounter::firstOrCreate(
                [
                    'appointment_id' =>
                        $lockedAppointment->id,
                ],
                [
                    'patient_id' =>
                        $lockedAppointment->patient_id,

                    'network_id' =>
                        $context->network_id,

                    'unit_id' =>
                        $context->unit_id,

                    'professional_user_id' =>
                        $lockedAppointment
                            ->professional_user_id,

                    'specialty_id' =>
                        $lockedAppointment
                            ->schedule
                            ?->specialty_id,

                    'service_location_id' =>
                        $lockedAppointment
                            ->schedule
                            ?->service_location_id,

                    'status' =>
                        'in_progress',

                    'started_at' =>
                        $startedAt,

                    'reason' =>
                        $lockedAppointment->reason,

                    'created_by_user_id' =>
                        $user->id,

                    'updated_by_user_id' =>
                        $user->id,
                ]
            );
        }
    );

    return back()->with(
        'success',
        'Atendimento iniciado e episódio clínico criado com sucesso.'
    );
}

/**
 * Finaliza o atendimento administrativo e clínico.
 */
public function complete(
    Request $request,
    Appointment $appointment
): RedirectResponse {
    $user = $request->user();

    $context = $this->activeContext($request);

    if (
        ! $context
        || (int) $appointment->network_id
            !== (int) $context->network_id
        || (int) $appointment->unit_id
            !== (int) $context->unit_id
    ) {
        abort(
            403,
            'Este agendamento não pertence à unidade atual.'
        );
    }

    $isResponsibleProfessional =
        (int) $appointment->professional_user_id
        === (int) $user->id;

    $canSupervise = $user->hasPermission(
        'medical_records.supervise'
    );

    if (
        ! $isResponsibleProfessional
        && ! $canSupervise
        && ! $user->isVitallisAdmin()
    ) {
        abort(
            403,
            'Somente o profissional responsável ou um supervisor autorizado pode finalizar este atendimento.'
        );
    }

    if ($appointment->status !== 'in_progress') {
        return back()->with(
            'error',
            'Somente atendimentos em andamento podem ser finalizados.'
        );
    }

    DB::transaction(
        function () use (
            $appointment,
            $user,
            $context
        ) {
            $lockedAppointment = Appointment::query()
                ->with([
                    'schedule',
                    'medicalEncounter',
                ])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAppointment->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'appointment' =>
                        'Este atendimento não está mais em andamento.',
                ]);
            }

            $completedAt = now();

            $lockedAppointment->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
            ]);

            /*
             * firstOrCreate cobre atendimentos iniciados antes
             * da implantação de medical_encounters.
             */
            $encounter = MedicalEncounter::firstOrCreate(
                [
                    'appointment_id' =>
                        $lockedAppointment->id,
                ],
                [
                    'patient_id' =>
                        $lockedAppointment->patient_id,

                    'network_id' =>
                        $context->network_id,

                    'unit_id' =>
                        $context->unit_id,

                    'professional_user_id' =>
                        $lockedAppointment
                            ->professional_user_id,

                    'specialty_id' =>
                        $lockedAppointment
                            ->schedule
                            ?->specialty_id,

                    'service_location_id' =>
                        $lockedAppointment
                            ->schedule
                            ?->service_location_id,

                    'status' =>
                        'in_progress',

                    'started_at' =>
                        $lockedAppointment->checked_in_at
                        ?? $completedAt,

                    'reason' =>
                        $lockedAppointment->reason,

                    'created_by_user_id' =>
                        $user->id,
                ]
            );

            $encounter->update([
                'status' =>
                    'completed',

                'completed_at' =>
                    $completedAt,

                'updated_by_user_id' =>
                    $user->id,

                'completed_by_user_id' =>
                    $user->id,
            ]);
        }
    );

    return back()->with(
        'success',
        'Atendimento finalizado com sucesso.'
    );
}

/**
 * Confirma o agendamento.
 */
public function confirm(
    Request $request,
    Appointment $appointment
): RedirectResponse {
    $context = $this->activeContext($request);

    if (
        ! $context
        || $appointment->network_id !== $context->network_id
        || $appointment->unit_id !== $context->unit_id
    ) {
        abort(403, 'Este agendamento não pertence à unidade atual.');
    }

    if ($appointment->status !== 'scheduled') {
        return back()->with(
            'error',
            'Somente agendamentos pendentes podem ser confirmados.'
        );
    }

    $appointment->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);

    return back()->with(
        'success',
        'Agendamento confirmado com sucesso.'
    );
}


/**
 * Cancela um agendamento.
 */
public function cancel(
    Request $request,
    Appointment $appointment
): RedirectResponse {
    $context = $this->activeContext($request);

    if (
        ! $context
        || $appointment->network_id !== $context->network_id
        || $appointment->unit_id !== $context->unit_id
    ) {
        abort(403, 'Este agendamento nÃ£o pertence Ã  unidade atual.');
    }

    if (! in_array($appointment->status, ['scheduled', 'confirmed'])) {
        return back()->with(
            'error',
            'Este agendamento nÃ£o pode mais ser cancelado.'
        );
    }

    $appointment->update([
        'status' => 'cancelled',
        'cancelled_at' => now(),
        'cancelled_by_user_id' => $request->user()->id,
    ]);

    return back()->with(
        'success',
        'Agendamento cancelado com sucesso.'
    );
}


/**
 * Tela para reagendar um atendimento.
 */
public function reschedule(
    Request $request,
    Appointment $appointment
): View {
    $context = $this->activeContext($request);

    if (
        ! $context
        || $appointment->network_id !== $context->network_id
        || $appointment->unit_id !== $context->unit_id
    ) {
        abort(403, 'Este agendamento nÃ£o pertence Ã  unidade atual.');
    }

    if (! in_array($appointment->status, ['scheduled', 'confirmed'])) {
        abort(422, 'Este agendamento nÃ£o pode ser reagendado.');
    }

    $appointment->load([
        'patient',
        'professional',
        'schedule.specialtyRelation',
    ]);

    $schedules = Schedule::query()
        ->with([
            'professional',
            'specialtyRelation',
            'serviceLocation',
        ])
        ->where('network_id', $context->network_id)
        ->where('unit_id', $context->unit_id)
        ->where('active', true)
        ->orderBy('professional_user_id')
        ->orderBy('weekday')
        ->orderBy('starts_at')
        ->get();

    return view('appointments.reschedule', [
        'appointment' => $appointment,
        'schedules' => $schedules,
        'context' => $context,
    ]);
}

/**
 * Salva o reagendamento.
 */
public function updateSchedule(
    Request $request,
    Appointment $appointment
): RedirectResponse {
    $context = $this->activeContext($request);

    if (
        ! $context
        || $appointment->network_id !== $context->network_id
        || $appointment->unit_id !== $context->unit_id
    ) {
        abort(403, 'Este agendamento nÃ£o pertence Ã  unidade atual.');
    }

    if (! in_array($appointment->status, ['scheduled', 'confirmed'])) {
        return back()->with(
            'error',
            'Este agendamento nÃ£o pode ser reagendado.'
        );
    }

    $validated = $request->validate([
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
    ]);

    $schedule = Schedule::query()
        ->whereKey($validated['schedule_id'])
        ->where('network_id', $context->network_id)
        ->where('unit_id', $context->unit_id)
        ->where('active', true)
        ->firstOrFail();

    $date = Carbon::createFromFormat(
        'Y-m-d',
        $validated['date']
    )->startOfDay();

    if ($date->dayOfWeek !== (int) $schedule->weekday) {
        throw ValidationException::withMessages([
            'date' => 'A grade selecionada nÃ£o possui atendimento neste dia.',
        ]);
    }

    if (
        $date->lt($schedule->starts_on->startOfDay())
        || (
            $schedule->ends_on
            && $date->gt($schedule->ends_on->startOfDay())
        )
    ) {
        throw ValidationException::withMessages([
            'date' => 'A data estÃ¡ fora da validade desta grade.',
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
        ->addMinutes($schedule->slot_duration_minutes);

    if (
        $startsAt->lt($scheduleStartsAt)
        || $endsAt->gt($scheduleEndsAt)
    ) {
        throw ValidationException::withMessages([
            'time' => 'O horÃ¡rio estÃ¡ fora da grade selecionada.',
        ]);
    }

    $minutesFromStart = $scheduleStartsAt->diffInMinutes(
        $startsAt,
        false
    );

    if (
        $minutesFromStart < 0
        || $minutesFromStart % $schedule->slot_duration_minutes !== 0
    ) {
        throw ValidationException::withMessages([
            'time' => 'O horÃ¡rio nÃ£o corresponde a um intervalo vÃ¡lido da grade.',
        ]);
    }

    DB::transaction(function () use (
        $appointment,
        $schedule,
        $startsAt,
        $endsAt
    ) {
        Schedule::query()
            ->whereKey($schedule->id)
            ->lockForUpdate()
            ->firstOrFail();

        $appointmentsAtSlot = Appointment::query()
            ->where('schedule_id', $schedule->id)
            ->where('starts_at', $startsAt)
            ->whereKeyNot($appointment->id)
            ->whereNotIn('status', [
                'cancelled',
                'no_show',
            ])
            ->count();

        if ($appointmentsAtSlot >= $schedule->slot_capacity) {
            throw ValidationException::withMessages([
                'time' => 'Este horÃ¡rio nÃ£o possui mais vagas disponÃ­veis.',
            ]);
        }

        $appointment->update([
            'schedule_id' => $schedule->id,
            'professional_user_id' => $schedule->professional_user_id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,

            // Reagendamento volta para pendente de confirmaÃ§Ã£o.
            'status' => 'scheduled',
            'confirmed_at' => null,
            'checked_in_at' => null,
        ]);
    });

    return redirect()
        ->route('appointments.index', [
            'date' => $startsAt->format('Y-m-d'),
        ])
        ->with(
            'success',
            'Agendamento reagendado com sucesso.'
        );
}

}
