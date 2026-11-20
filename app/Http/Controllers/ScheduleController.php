<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ServiceLocation;
use App\Models\Specialty;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Lista as grades da unidade ativa.
     */
    public function index(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho para acessar as grades.'
            );
        }

        $unit = Unit::query()
            ->with('network')
            ->whereKey($context->unit_id)
            ->where(
                'network_id',
                $context->network_id
            )
            ->firstOrFail();

        $schedules = Schedule::query()
            ->with([
                'professional',
                'specialtyRelation',
                'serviceLocation.parent',
            ])
            ->where(
                'network_id',
                $context->network_id
            )
            ->where(
                'unit_id',
                $context->unit_id
            )
            ->orderBy('professional_user_id')
            ->orderBy('weekday')
            ->orderBy('starts_at')
            ->get();

        return view(
            'schedules.index',
            compact(
                'unit',
                'schedules'
            )
        );
    }

    /**
     * Formulário para criação de grade.
     */
    public function create(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de criar uma grade.'
            );
        }

        $unit = Unit::query()
            ->with('network')
            ->whereKey($context->unit_id)
            ->where(
                'network_id',
                $context->network_id
            )
            ->where('active', true)
            ->firstOrFail();

        $professionals = $this->professionalsForContext(
            $context
        );

        $specialties = Specialty::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        /*
         * Um local de atendimento só pode ser selecionado
         * se pertencer à unidade ativa.
         */
        $serviceLocations = ServiceLocation::query()
            ->with('parent')
            ->where(
                'unit_id',
                $context->unit_id
            )
            ->where('active', true)
            ->orderByRaw(
                "CASE type
                    WHEN 'building' THEN 1
                    WHEN 'block' THEN 2
                    WHEN 'floor' THEN 3
                    WHEN 'wing' THEN 4
                    WHEN 'sector' THEN 5
                    WHEN 'room' THEN 6
                    WHEN 'office' THEN 7
                    WHEN 'bed' THEN 8
                    ELSE 9
                END"
            )
            ->orderBy('name')
            ->get();

        return view(
            'schedules.create',
            compact(
                'unit',
                'professionals',
                'specialties',
                'serviceLocations'
            )
        );
    }

    /**
     * Salva uma nova grade.
     */
    public function store(Request $request): RedirectResponse
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de criar uma grade.'
            );
        }

        $validated = $this->validateSchedule(
            $request
        );

        $this->validateScheduleContext(
            $validated,
            $context
        );

        $this->validateSchedulePeriod(
            $validated
        );

        $this->validateProfessionalAccess(
            (int) $validated['professional_user_id'],
            $context
        );

        $this->validateSpecialty(
            (int) $validated['specialty_id']
        );

        $this->validateServiceLocation(
            (int) $validated['service_location_id'],
            $context
        );

        Schedule::create([
            'network_id' =>
                $context->network_id,

            'unit_id' =>
                $context->unit_id,

            'professional_user_id' =>
                $validated['professional_user_id'],

            'name' =>
                $this->nullableTrim(
                    $validated['name'] ?? null
                ),

            'specialty_id' =>
                $validated['specialty_id'],

            'service_location_id' =>
                $validated['service_location_id'],

            /*
             * Campos legados.
             *
             * As novas grades não gravam mais especialidade
             * ou local como texto livre.
             */
            'specialty' => null,
            'room' => null,

            'starts_on' =>
                $validated['starts_on'],

            'ends_on' =>
                $validated['ends_on'] ?? null,

            'weekday' =>
                $validated['weekday'],

            'starts_at' =>
                $validated['starts_at'],

            'ends_at' =>
                $validated['ends_at'],

            'slot_duration_minutes' =>
                $validated['slot_duration_minutes'],

            'slot_capacity' =>
                $validated['slot_capacity'],

            'active' => true,
        ]);

        return redirect()
            ->route('schedules.index')
            ->with(
                'success',
                'Grade de agenda criada com sucesso.'
            );
    }

    /**
     * Formulário de edição da grade.
     */
    public function edit(
        Request $request,
        Schedule $schedule
    ): View {
        $context = $this->activeContext($request);

        $this->authorizeScheduleContext(
            $schedule,
            $context
        );

        $unit = Unit::query()
            ->with('network')
            ->whereKey($context->unit_id)
            ->where(
                'network_id',
                $context->network_id
            )
            ->firstOrFail();

        $professionals = $this->professionalsForContext(
            $context
        );

        $specialties = Specialty::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        $serviceLocations = ServiceLocation::query()
            ->with('parent')
            ->where(
                'unit_id',
                $context->unit_id
            )
            ->where('active', true)
            ->orderByRaw(
                "CASE type
                    WHEN 'building' THEN 1
                    WHEN 'block' THEN 2
                    WHEN 'floor' THEN 3
                    WHEN 'wing' THEN 4
                    WHEN 'sector' THEN 5
                    WHEN 'room' THEN 6
                    WHEN 'office' THEN 7
                    WHEN 'bed' THEN 8
                    ELSE 9
                END"
            )
            ->orderBy('name')
            ->get();

        return view(
            'schedules.edit',
            compact(
                'schedule',
                'unit',
                'professionals',
                'specialties',
                'serviceLocations'
            )
        );
    }

    /**
     * Atualiza uma grade.
     */
    public function update(
        Request $request,
        Schedule $schedule
    ): RedirectResponse {
        $context = $this->activeContext($request);

        $this->authorizeScheduleContext(
            $schedule,
            $context
        );

        $validated = $this->validateSchedule(
            $request
        );

        $this->validateScheduleContext(
            $validated,
            $context
        );

        $this->validateSchedulePeriod(
            $validated
        );

        $this->validateProfessionalAccess(
            (int) $validated['professional_user_id'],
            $context
        );

        $this->validateSpecialty(
            (int) $validated['specialty_id']
        );

        $this->validateServiceLocation(
            (int) $validated['service_location_id'],
            $context
        );

        $schedule->update([
            'professional_user_id' =>
                $validated['professional_user_id'],

            'name' =>
                $this->nullableTrim(
                    $validated['name'] ?? null
                ),

            'specialty_id' =>
                $validated['specialty_id'],

            'service_location_id' =>
                $validated['service_location_id'],

            'specialty' => null,
            'room' => null,

            'starts_on' =>
                $validated['starts_on'],

            'ends_on' =>
                $validated['ends_on'] ?? null,

            'weekday' =>
                $validated['weekday'],

            'starts_at' =>
                $validated['starts_at'],

            'ends_at' =>
                $validated['ends_at'],

            'slot_duration_minutes' =>
                $validated['slot_duration_minutes'],

            'slot_capacity' =>
                $validated['slot_capacity'],
        ]);

        return redirect()
            ->route('schedules.index')
            ->with(
                'success',
                'Grade de agenda atualizada com sucesso.'
            );
    }

    /**
     * Validação principal da grade.
     */
    private function validateSchedule(
        Request $request
    ): array {
        return $request->validate([
            'professional_user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'specialty_id' => [
                'required',
                'integer',
                'exists:specialties,id',
            ],

            'service_location_id' => [
                'required',
                'integer',
                'exists:service_locations,id',
            ],

            'starts_on' => [
                'required',
                'date',
            ],

            'ends_on' => [
                'nullable',
                'date',
                'after_or_equal:starts_on',
            ],

            'weekday' => [
                'required',
                'integer',
                'between:0,6',
            ],

            'starts_at' => [
                'required',
                'date_format:H:i',
            ],

            'ends_at' => [
                'required',
                'date_format:H:i',
            ],

            'slot_duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:480',
            ],

            'slot_capacity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);
    }

    /**
     * Valida o período diário da grade.
     */
    private function validateSchedulePeriod(
        array $validated
    ): void {
        if (
            $validated['ends_at']
            <= $validated['starts_at']
        ) {
            throw ValidationException::withMessages([
                'ends_at' =>
                    'O horário final deve ser posterior ao horário inicial.',
            ]);
        }

        $startMinutes = $this->timeToMinutes(
            $validated['starts_at']
        );

        $endMinutes = $this->timeToMinutes(
            $validated['ends_at']
        );

        $availableMinutes =
            $endMinutes - $startMinutes;

        if (
            $validated['slot_duration_minutes']
            > $availableMinutes
        ) {
            throw ValidationException::withMessages([
                'slot_duration_minutes' =>
                    'A duração do atendimento é maior que o período disponível na grade.',
            ]);
        }
    }

    /**
     * Confirma que os dados pertencem ao contexto atual.
     */
    private function validateScheduleContext(
        array $validated,
        object $context
    ): void {
        Unit::query()
            ->whereKey($context->unit_id)
            ->where(
                'network_id',
                $context->network_id
            )
            ->where('active', true)
            ->firstOrFail();
    }

    /**
     * Confirma que o profissional está disponível
     * no contexto da unidade atual.
     */
    private function validateProfessionalAccess(
        int $professionalUserId,
        object $context
    ): void {
        $professionalHasAccess = DB::table(
            'role_user'
        )
            ->where(
                'user_id',
                $professionalUserId
            )
            ->where(
                'network_id',
                $context->network_id
            )
            ->where('active', true)
            ->where(
                function ($query) use ($context) {
                    $query
                        ->where(
                            'unit_id',
                            $context->unit_id
                        )
                        ->orWhereNull('unit_id');
                }
            )
            ->exists();

        if (! $professionalHasAccess) {
            throw ValidationException::withMessages([
                'professional_user_id' =>
                    'O profissional selecionado não possui vínculo ativo com esta unidade.',
            ]);
        }
    }

    /**
     * Confirma que a especialidade está ativa.
     */
    private function validateSpecialty(
        int $specialtyId
    ): void {
        $specialtyExists = Specialty::query()
            ->whereKey($specialtyId)
            ->where('active', true)
            ->exists();

        if (! $specialtyExists) {
            throw ValidationException::withMessages([
                'specialty_id' =>
                    'A especialidade selecionada não está disponível.',
            ]);
        }
    }

    /**
     * Confirma que o local pertence à unidade ativa.
     */
    private function validateServiceLocation(
        int $serviceLocationId,
        object $context
    ): void {
        $locationExists = ServiceLocation::query()
            ->whereKey($serviceLocationId)
            ->where(
                'unit_id',
                $context->unit_id
            )
            ->where('active', true)
            ->exists();

        if (! $locationExists) {
            throw ValidationException::withMessages([
                'service_location_id' =>
                    'O local de atendimento selecionado não pertence à unidade atual.',
            ]);
        }
    }

    /**
     * Protege edição direta por URL.
     */
    private function authorizeScheduleContext(
        Schedule $schedule,
        ?object $context
    ): void {
        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho.'
            );
        }

        if (
            (int) $schedule->network_id
                !== (int) $context->network_id
            || (int) $schedule->unit_id
                !== (int) $context->unit_id
        ) {
            abort(
                403,
                'Esta grade pertence a outra unidade.'
            );
        }
    }

    /**
     * Retorna os profissionais disponíveis no contexto.
     */
    private function professionalsForContext(
        object $context
    ) {
        $professionalIds = DB::table('role_user')
            ->where(
                'network_id',
                $context->network_id
            )
            ->where('active', true)
            ->where(
                function ($query) use ($context) {
                    $query
                        ->where(
                            'unit_id',
                            $context->unit_id
                        )
                        ->orWhereNull('unit_id');
                }
            )
            ->pluck('user_id')
            ->unique();

        return User::query()
            ->where('active', true)
            ->whereIn(
                'id',
                $professionalIds
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);
    }

    /**
     * Converte HH:MM em minutos.
     */
    private function timeToMinutes(
        string $time
    ): int {
        [$hours, $minutes] = array_map(
            'intval',
            explode(':', $time)
        );

        return ($hours * 60) + $minutes;
    }

    /**
     * Normaliza texto opcional.
     */
    private function nullableTrim(
        ?string $value
    ): ?string {
        if (
            $value === null
            || trim($value) === ''
        ) {
            return null;
        }

        return trim($value);
    }

    /**
     * Resolve o contexto ativo.
     */
    private function activeContext(
        Request $request
    ): ?object {
        $networkId = $request
            ->session()
            ->get('active_network_id');

        $unitId = $request
            ->session()
            ->get('active_unit_id');

        if ($networkId && $unitId) {
            return (object) [
                'network_id' => (int) $networkId,
                'unit_id' => (int) $unitId,
            ];
        }

        $context = DB::table('user_contexts')
            ->where(
                'user_id',
                $request->user()->id
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