<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Network;
use App\Models\Patient;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Lista somente os pacientes acessíveis ao usuário.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $search = trim(
            (string) $request->get('search')
        );

        $networkId = $request->integer(
            'network_id'
        ) ?: null;

        $accessibleNetworkIds = $this
            ->accessibleNetworkIds($request);

        $patients = Patient::query()
            ->with([
                'networks' => function ($query) use (
                    $user,
                    $accessibleNetworkIds
                ) {
                    $query->wherePivot(
                        'active',
                        true
                    );

                    if (! $user->isVitallisAdmin()) {
                        $query->whereIn(
                            'networks.id',
                            $accessibleNetworkIds
                        );
                    }
                },
            ])
            ->when(
                ! $user->isVitallisAdmin(),
                function (
                    Builder $query
                ) use (
                    $accessibleNetworkIds
                ) {
                    $query->whereHas(
                        'networks',
                        function (
                            Builder $networkQuery
                        ) use (
                            $accessibleNetworkIds
                        ) {
                            $networkQuery
                                ->whereIn(
                                    'networks.id',
                                    $accessibleNetworkIds
                                )
                                ->where(
                                    'patient_network.active',
                                    true
                                );
                        }
                    );
                }
            )
            ->when(
                $networkId !== null,
                function (
                    Builder $query
                ) use (
                    $networkId,
                    $user,
                    $accessibleNetworkIds
                ) {
                    /*
                     * Impede que um network_id manipulado
                     * na URL amplie o acesso do usuário.
                     */
                    if (
                        ! $user->isVitallisAdmin()
                        && ! in_array(
                            $networkId,
                            $accessibleNetworkIds,
                            true
                        )
                    ) {
                        abort(
                            403,
                            'Você não possui acesso a esta rede.'
                        );
                    }

                    $query->whereHas(
                        'networks',
                        function (
                            Builder $networkQuery
                        ) use (
                            $networkId
                        ) {
                            $networkQuery
                                ->where(
                                    'networks.id',
                                    $networkId
                                )
                                ->where(
                                    'patient_network.active',
                                    true
                                );
                        }
                    );
                }
            )
            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use (
                    $search
                ) {
                    $onlyNumbers = preg_replace(
                        '/\D+/',
                        '',
                        $search
                    );

                    $query->where(
                        function (
                            Builder $searchQuery
                        ) use (
                            $search,
                            $onlyNumbers
                        ) {
                            $searchQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'social_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'mother_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );

                            if ($onlyNumbers !== '') {
                                $searchQuery
                                    ->orWhere(
                                        'cpf',
                                        'like',
                                        "%{$onlyNumbers}%"
                                    )
                                    ->orWhere(
                                        'cns',
                                        'like',
                                        "%{$onlyNumbers}%"
                                    )
                                    ->orWhere(
                                        'phone',
                                        'like',
                                        "%{$onlyNumbers}%"
                                    )
                                    ->orWhere(
                                        'mobile_phone',
                                        'like',
                                        "%{$onlyNumbers}%"
                                    );
                            }
                        }
                    );
                }
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $networks = $this->accessibleNetworks(
            $request
        );

        return view(
            'patients.index',
            compact(
                'patients',
                'networks',
                'search',
                'networkId'
            )
        );
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function create(Request $request): View
    {
        $networks = $this->accessibleNetworks(
            $request
        );

        $units = $this->accessibleUnits(
            $request
        );

        return view(
            'patients.create',
            compact(
                'networks',
                'units'
            )
        );
    }

    /**
     * Cadastra o paciente e cria o vínculo com a rede.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validatePatient(
            $request
        );

        $networkId = (int) $validated[
            'network_id'
        ];

        $this->authorizeNetwork(
            $request,
            $networkId
        );

        $originUnitId = ! empty(
            $validated['origin_unit_id']
        )
            ? (int) $validated['origin_unit_id']
            : null;

        $this->validateOriginUnit(
            $request,
            $networkId,
            $originUnitId
        );

        $patientData = $this->patientData(
            $validated
        );

        DB::transaction(
            function () use (
                $patientData,
                $networkId,
                $originUnitId
            ) {
                $patient = Patient::create(
                    $patientData
                );

                $patient->networks()->attach(
                    $networkId,
                    [
                        'origin_unit_id' =>
                            $originUnitId,

                        'active' =>
                            true,

                        'first_registered_at' =>
                            now(),

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]
                );
            }
        );

        return redirect()
            ->route('patients.index')
            ->with(
                'success',
                'Paciente cadastrado com sucesso.'
            );
    }

    /**
     * Exibe o prontuário longitudinal do paciente.
     *
     * A tela funciona em dois modos:
     *
     * view:
     *     consulta ao histórico, sem alterações clínicas;
     *
     * edit:
     *     edição clínica durante atendimento em andamento,
     *     supervisão ou autorização excepcional.
     */
    public function show(
        Request $request,
        Patient $patient
    ): View {
        $this->authorizePatientAccess(
            $request,
            $patient
        );

        $user = $request->user();

        $context = $this->activeContext(
            $request
        );

        $patient->load([
            'networks' => function ($query) {
                $query->wherePivot(
                    'active',
                    true
                );
            },
            'users',
        ]);

        /*
         * Atendimento administrativo em andamento
         * dentro da unidade ativa.
         */
        $activeAppointment = null;

        if ($context) {
            $activeAppointment = Appointment::query()
                ->with([
                    'professional',
                    'schedule.specialtyRelation',
                    'schedule.serviceLocation',
                    'unit',
                    'medicalEncounter',
                ])
                ->where(
                    'patient_id',
                    $patient->id
                )
                ->where(
                    'network_id',
                    $context->network_id
                )
                ->where(
                    'unit_id',
                    $context->unit_id
                )
                ->where(
                    'status',
                    'in_progress'
                )
                ->orderByDesc('starts_at')
                ->first();
        }

        /*
         * O usuário atual é o profissional responsável
         * pelo atendimento em andamento?
         */
        $isResponsibleProfessional =
            $activeAppointment !== null
            && (int) $activeAppointment
                ->professional_user_id
                === (int) $user->id;

        /*
         * Exceções autorizadas pelo RBAC.
         */
        $canWriteOutsideEncounter =
            $user->hasPermission(
                'medical_records.write_outside_encounter'
            );

        $canSupervise =
            $user->hasPermission(
                'medical_records.supervise'
            );

        /*
         * A edição clínica é permitida quando:
         *
         * 1. o usuário é o profissional responsável
         *    pelo atendimento em andamento;
         *
         * 2. possui permissão de supervisão;
         *
         * 3. possui permissão para alteração
         *    fora do atendimento.
         */
        $canEditClinicalRecord =
            $isResponsibleProfessional
            || $canSupervise
            || $canWriteOutsideEncounter;

        $recordMode = $canEditClinicalRecord
            ? 'edit'
            : 'view';

        /*
         * Histórico longitudinal de episódios clínicos.
         *
         * MedicalEncounter representa o episódio clínico real.
         * Appointment representa o fluxo administrativo
         * da agenda.
         */
        $medicalEncounters = $patient
            ->medicalEncounters()
            ->with([
                'appointment',
                'professional',
                'specialty',
                'serviceLocation',
                'unit',
                'createdBy',
                'completedBy',
            ])
            ->when(
                $context !== null,
                function ($query) use ($context) {
                    $query->where(
                        'network_id',
                        $context->network_id
                    );
                }
            )
            ->orderByDesc('started_at')
            ->get();

        /*
         * Abas visíveis no fichário.
         *
         * A view utiliza essas autorizações para não
         * apresentar conteúdos que o perfil não pode consultar.
         */
        $tabs = [
            'summary' => true,

            'registration' => true,

            'encounters' => $user->hasPermission(
                'medical_records.view'
            ),

            'evolutions' => $user->hasPermission(
                'medical_records.view'
            ),

            'diagnoses' => $user->hasPermission(
                'medical_records.view'
            ),

            'prescriptions' => $user->hasPermission(
                'prescriptions.view'
            ),

            'exams' => $user->hasPermission(
                'exams.view'
            ),

            /*
             * Ainda não existem permissões próprias para
             * escalas, documentos e alta.
             *
             * Temporariamente acompanham
             * medical_records.view.
             */
            'scales' => $user->hasPermission(
                'medical_records.view'
            ),

            'documents' => $user->hasPermission(
                'medical_records.view'
            ),

            'discharge' => $user->hasPermission(
                'medical_records.view'
            ),
        ];

        return view(
            'patients.show',
            compact(
                'patient',
                'activeAppointment',
                'medicalEncounters',
                'isResponsibleProfessional',
                'canWriteOutsideEncounter',
                'canSupervise',
                'canEditClinicalRecord',
                'recordMode',
                'tabs',
                'context'
            )
        );
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(
        Request $request,
        Patient $patient
    ): View {
        $this->authorizePatientAccess(
            $request,
            $patient
        );

        $patient->load([
            'networks' => function ($query) {
                $query->wherePivot(
                    'active',
                    true
                );
            },
            'users',
        ]);

        $networks = $this->accessibleNetworks(
            $request
        );

        $units = $this->accessibleUnits(
            $request
        );

        return view(
            'patients.edit',
            compact(
                'patient',
                'networks',
                'units'
            )
        );
    }

    /**
     * Atualiza o cadastro mestre do paciente.
     *
     * O vínculo com a rede não é alterado
     * silenciosamente aqui.
     */
    public function update(
        Request $request,
        Patient $patient
    ): RedirectResponse {
        $this->authorizePatientAccess(
            $request,
            $patient
        );

        $validated = $this->validatePatient(
            $request,
            $patient
        );

        $patient->update(
            $this->patientData($validated)
        );

        return redirect()
            ->route('patients.index')
            ->with(
                'success',
                'Paciente atualizado com sucesso.'
            );
    }

    /**
     * Ativa ou desativa o cadastro do paciente.
     */
    public function toggleStatus(
        Request $request,
        Patient $patient
    ): RedirectResponse {
        $this->authorizePatientAccess(
            $request,
            $patient
        );

        $patient->update([
            'active' => ! $patient->active,
        ]);

        return redirect()
            ->route('patients.index')
            ->with(
                'success',
                $patient->active
                    ? 'Paciente ativado com sucesso.'
                    : 'Paciente desativado com sucesso.'
            );
    }

    /**
     * Valida os dados cadastrais do paciente.
     */
    private function validatePatient(
        Request $request,
        ?Patient $patient = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'social_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'cpf' => [
                'nullable',
                'string',
                'max:14',

                Rule::unique(
                    'patients',
                    'cpf'
                )->ignore($patient?->id),
            ],

            'cns' => [
                'nullable',
                'string',
                'max:20',

                Rule::unique(
                    'patients',
                    'cns'
                )->ignore($patient?->id),
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'sex' => [
                'nullable',
                'string',
                'max:30',
            ],

            'mother_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'father_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'mobile_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'street' => [
                'nullable',
                'string',
                'max:255',
            ],

            'number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'complement' => [
                'nullable',
                'string',
                'max:255',
            ],

            'district' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'state' => [
                'nullable',
                'string',
                'size:2',
            ],

            /*
             * Utilizados no cadastro inicial.
             *
             * Na edição, não modificamos automaticamente
             * o vínculo patient_network.
             */
            'network_id' => [
                Rule::requiredIf(
                    $patient === null
                ),
                'nullable',
                'integer',
                'exists:networks,id',
            ],

            'origin_unit_id' => [
                'nullable',
                'integer',
                'exists:units,id',
            ],
        ]);
    }

    /**
     * Monta somente os campos pertencentes a patients.
     */
    private function patientData(
        array $validated
    ): array {
        return [
            'name' =>
                $validated['name'],

            'social_name' =>
                $validated['social_name'] ?? null,

            'cpf' =>
                $this->numbersOnly(
                    $validated['cpf'] ?? null
                ),

            'cns' =>
                $this->numbersOnly(
                    $validated['cns'] ?? null
                ),

            'birth_date' =>
                $validated['birth_date'] ?? null,

            'sex' =>
                $validated['sex'] ?? null,

            'mother_name' =>
                $validated['mother_name'] ?? null,

            'father_name' =>
                $validated['father_name'] ?? null,

            'email' =>
                $validated['email'] ?? null,

            'phone' =>
                $this->numbersOnly(
                    $validated['phone'] ?? null
                ),

            'mobile_phone' =>
                $this->numbersOnly(
                    $validated['mobile_phone'] ?? null
                ),

            'postal_code' =>
                $this->numbersOnly(
                    $validated['postal_code'] ?? null
                ),

            'street' =>
                $validated['street'] ?? null,

            'number' =>
                $validated['number'] ?? null,

            'complement' =>
                $validated['complement'] ?? null,

            'district' =>
                $validated['district'] ?? null,

            'city' =>
                $validated['city'] ?? null,

            'state' => isset(
                $validated['state']
            )
                ? strtoupper(
                    $validated['state']
                )
                : null,

            'active' => true,
        ];
    }

    /**
     * Retorna os IDs das redes acessíveis pelo usuário.
     */
    private function accessibleNetworkIds(
        Request $request
    ): array {
        $user = $request->user();

        if ($user->isVitallisAdmin()) {
            return Network::query()
                ->pluck('id')
                ->map(
                    fn ($id) => (int) $id
                )
                ->all();
        }

        return DB::table('role_user')
            ->where(
                'user_id',
                $user->id
            )
            ->where('active', true)
            ->whereNotNull('network_id')
            ->distinct()
            ->pluck('network_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->all();
    }

    /**
     * Consulta as redes acessíveis e ativas.
     */
    private function accessibleNetworks(
        Request $request
    ) {
        $query = Network::query()
            ->where('active', true)
            ->orderBy('name');

        if (! $request->user()->isVitallisAdmin()) {
            $query->whereIn(
                'id',
                $this->accessibleNetworkIds(
                    $request
                )
            );
        }

        return $query->get([
            'id',
            'name',
        ]);
    }

    /**
     * Consulta as unidades acessíveis e ativas.
     */
    private function accessibleUnits(
        Request $request
    ) {
        $user = $request->user();

        $query = Unit::query()
            ->where('active', true)
            ->orderBy('name');

        if ($user->isVitallisAdmin()) {
            return $query->get([
                'id',
                'network_id',
                'name',
            ]);
        }

        $links = DB::table('role_user')
            ->where(
                'user_id',
                $user->id
            )
            ->where('active', true)
            ->whereNotNull('network_id')
            ->get([
                'network_id',
                'unit_id',
            ]);

        $networkWideIds = $links
            ->whereNull('unit_id')
            ->pluck('network_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values()
            ->all();

        $specificUnitIds = $links
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values()
            ->all();

        $query->where(
            function (
                Builder $unitQuery
            ) use (
                $networkWideIds,
                $specificUnitIds
            ) {
                if ($networkWideIds !== []) {
                    $unitQuery->whereIn(
                        'network_id',
                        $networkWideIds
                    );
                }

                if ($specificUnitIds !== []) {
                    $method = $networkWideIds !== []
                        ? 'orWhereIn'
                        : 'whereIn';

                    $unitQuery->{$method}(
                        'id',
                        $specificUnitIds
                    );
                }

                if (
                    $networkWideIds === []
                    && $specificUnitIds === []
                ) {
                    $unitQuery->whereRaw(
                        '1 = 0'
                    );
                }
            }
        );

        return $query->get([
            'id',
            'network_id',
            'name',
        ]);
    }

    /**
     * Verifica se o usuário pode operar na rede.
     */
    private function authorizeNetwork(
        Request $request,
        int $networkId
    ): void {
        if ($request->user()->isVitallisAdmin()) {
            return;
        }

        if (
            ! in_array(
                $networkId,
                $this->accessibleNetworkIds(
                    $request
                ),
                true
            )
        ) {
            abort(
                403,
                'Você não possui acesso a esta rede.'
            );
        }
    }

    /**
     * Valida a unidade de origem dentro da rede.
     */
    private function validateOriginUnit(
        Request $request,
        int $networkId,
        ?int $originUnitId
    ): void {
        if ($originUnitId === null) {
            return;
        }

        $unit = Unit::query()
            ->whereKey($originUnitId)
            ->where(
                'network_id',
                $networkId
            )
            ->where('active', true)
            ->first();

        if (! $unit) {
            abort(
                422,
                'A unidade selecionada não pertence à rede.'
            );
        }

        if ($request->user()->isVitallisAdmin()) {
            return;
        }

        $accessibleUnitIds = $this
            ->accessibleUnits($request)
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->all();

        if (
            ! in_array(
                $originUnitId,
                $accessibleUnitIds,
                true
            )
        ) {
            abort(
                403,
                'Você não possui acesso a esta unidade.'
            );
        }
    }

    /**
     * Protege o acesso direto ao paciente por URL.
     */
    private function authorizePatientAccess(
        Request $request,
        Patient $patient
    ): void {
        if ($request->user()->isVitallisAdmin()) {
            return;
        }

        $hasAccess = $patient
            ->networks()
            ->wherePivot(
                'active',
                true
            )
            ->whereIn(
                'networks.id',
                $this->accessibleNetworkIds(
                    $request
                )
            )
            ->exists();

        if (! $hasAccess) {
            abort(
                403,
                'Você não possui acesso a este paciente.'
            );
        }
    }

    /**
     * Resolve o contexto operacional ativo do usuário.
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

        /*
         * Fallback para o contexto persistido.
         */
        $context = DB::table('user_contexts')
            ->where(
                'user_id',
                $request->user()->id
            )
            ->first([
                'network_id',
                'unit_id',
            ]);

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
     * Normaliza documentos, telefones e CEP.
     */
    private function numbersOnly(
        ?string $value
    ): ?string {
        if (
            $value === null
            || trim($value) === ''
        ) {
            return null;
        }

        return preg_replace(
            '/\D+/',
            '',
            $value
        );
    }
}