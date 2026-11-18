<?php

namespace App\Http\Controllers;

use App\Models\ServiceLocation;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceLocationController extends Controller
{
    /**
     * Tipos de locais suportados pelo Vitallis.
     */
    private const LOCATION_TYPES = [
        'building' => 'Edifício',
        'block' => 'Bloco',
        'floor' => 'Andar',
        'wing' => 'Ala',
        'sector' => 'Setor',
        'room' => 'Sala',
        'office' => 'Consultório',
        'bed' => 'Leito',
        'other' => 'Outro',
    ];

    /**
     * Lista os locais de atendimento da unidade ativa.
     */
    public function index(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho para acessar os locais de atendimento.'
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

        $search = trim(
            (string) $request->get('search')
        );

        $type = trim(
            (string) $request->get('type')
        );

        $locations = ServiceLocation::query()
            ->with('parent')
            ->where(
                'unit_id',
                $unit->id
            )
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $type !== '',
                fn ($query) => $query->where(
                    'type',
                    $type
                )
            )
            ->orderBy('parent_id')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view(
            'service-locations.index',
            [
                'unit' => $unit,
                'locations' => $locations,
                'types' => self::LOCATION_TYPES,
                'search' => $search,
                'type' => $type,
            ]
        );
    }

    /**
     * Formulário de criação.
     */
    public function create(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de cadastrar um local.'
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

        /*
         * Um local só pode ter como pai outro local
         * pertencente à mesma unidade.
         */
        $parents = ServiceLocation::query()
            ->where(
                'unit_id',
                $unit->id
            )
            ->where('active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'type',
                'name',
                'code',
            ]);

        return view(
            'service-locations.create',
            [
                'unit' => $unit,
                'parents' => $parents,
                'types' => self::LOCATION_TYPES,
            ]
        );
    }

    /**
     * Cadastra um local de atendimento.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de cadastrar um local.'
            );
        }

        $unit = Unit::query()
            ->whereKey($context->unit_id)
            ->where(
                'network_id',
                $context->network_id
            )
            ->where('active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'exists:service_locations,id',
            ],

            'type' => [
                'required',
                Rule::in(
                    array_keys(
                        self::LOCATION_TYPES
                    )
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'nullable',
                'string',
                'max:50',
            ],

            'description' => [
                'nullable',
                'string',
                'max:65535',
            ],
        ]);

        $parentId = ! empty(
            $validated['parent_id']
        )
            ? (int) $validated['parent_id']
            : null;

        /*
         * Nunca aceita um parent_id de outra unidade,
         * mesmo que o HTML seja manipulado.
         */
        if ($parentId !== null) {
            $parentExists = ServiceLocation::query()
                ->whereKey($parentId)
                ->where(
                    'unit_id',
                    $unit->id
                )
                ->where('active', true)
                ->exists();

            if (! $parentExists) {
                throw ValidationException::withMessages([
                    'parent_id' =>
                        'O local superior selecionado não pertence à unidade atual.',
                ]);
            }
        }

        $code = ! empty($validated['code'])
            ? strtoupper(
                trim($validated['code'])
            )
            : null;

        /*
         * Código é único dentro da unidade.
         */
        if (
            $code !== null
            && ServiceLocation::query()
                ->where(
                    'unit_id',
                    $unit->id
                )
                ->where(
                    'code',
                    $code
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' =>
                    'Já existe um local com este código nesta unidade.',
            ]);
        }

        ServiceLocation::create([
            'unit_id' => $unit->id,
            'parent_id' => $parentId,
            'type' => $validated['type'],
            'name' => trim(
                $validated['name']
            ),
            'code' => $code,
            'description' =>
                $validated['description'] ?? null,
            'active' => true,
        ]);

        return redirect()
            ->route('service-locations.index')
            ->with(
                'success',
                'Local de atendimento cadastrado com sucesso.'
            );
    }

    /**
     * Formulário de edição.
     */
    public function edit(
        Request $request,
        ServiceLocation $serviceLocation
    ): View {
        $this->authorizeLocationContext(
            $request,
            $serviceLocation
        );

        $unit = Unit::query()
            ->with('network')
            ->findOrFail(
                $serviceLocation->unit_id
            );

        /*
         * Impede selecionar o próprio registro como pai.
         */
        $parents = ServiceLocation::query()
            ->where(
                'unit_id',
                $serviceLocation->unit_id
            )
            ->where(
                'id',
                '!=',
                $serviceLocation->id
            )
            ->where('active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'type',
                'name',
                'code',
            ]);

        return view(
            'service-locations.edit',
            [
                'serviceLocation' =>
                    $serviceLocation,

                'unit' => $unit,
                'parents' => $parents,
                'types' => self::LOCATION_TYPES,
            ]
        );
    }

    /**
     * Atualiza um local.
     */
    public function update(
        Request $request,
        ServiceLocation $serviceLocation
    ): RedirectResponse {
        $this->authorizeLocationContext(
            $request,
            $serviceLocation
        );

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'exists:service_locations,id',
            ],

            'type' => [
                'required',
                Rule::in(
                    array_keys(
                        self::LOCATION_TYPES
                    )
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'nullable',
                'string',
                'max:50',
            ],

            'description' => [
                'nullable',
                'string',
                'max:65535',
            ],
        ]);

        $parentId = ! empty(
            $validated['parent_id']
        )
            ? (int) $validated['parent_id']
            : null;

        if (
            $parentId !== null
            && $parentId === $serviceLocation->id
        ) {
            throw ValidationException::withMessages([
                'parent_id' =>
                    'Um local não pode ser superior de si mesmo.',
            ]);
        }

        if ($parentId !== null) {
            $parent = ServiceLocation::query()
                ->whereKey($parentId)
                ->where(
                    'unit_id',
                    $serviceLocation->unit_id
                )
                ->where('active', true)
                ->first();

            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_id' =>
                        'O local superior selecionado não pertence à mesma unidade.',
                ]);
            }

            /*
             * Protege contra ciclos simples da hierarquia.
             *
             * Exemplo proibido:
             * Bloco A -> Ala X -> Bloco A.
             */
            if (
                $this->wouldCreateCycle(
                    $serviceLocation,
                    $parent
                )
            ) {
                throw ValidationException::withMessages([
                    'parent_id' =>
                        'Esta seleção criaria um ciclo na hierarquia dos locais.',
                ]);
            }
        }

        $code = ! empty($validated['code'])
            ? strtoupper(
                trim($validated['code'])
            )
            : null;

        if (
            $code !== null
            && ServiceLocation::query()
                ->where(
                    'unit_id',
                    $serviceLocation->unit_id
                )
                ->where(
                    'code',
                    $code
                )
                ->where(
                    'id',
                    '!=',
                    $serviceLocation->id
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'code' =>
                    'Já existe outro local com este código nesta unidade.',
            ]);
        }

        $serviceLocation->update([
            'parent_id' => $parentId,
            'type' => $validated['type'],
            'name' => trim(
                $validated['name']
            ),
            'code' => $code,
            'description' =>
                $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('service-locations.index')
            ->with(
                'success',
                'Local de atendimento atualizado com sucesso.'
            );
    }

    /**
     * Ativa ou desativa o local.
     */
    public function toggleStatus(
        Request $request,
        ServiceLocation $serviceLocation
    ): RedirectResponse {
        $this->authorizeLocationContext(
            $request,
            $serviceLocation
        );

        /*
         * Não desativa um pai enquanto possuir
         * filhos ativos abaixo dele.
         */
        if (
            $serviceLocation->active
            && $serviceLocation
                ->children()
                ->where('active', true)
                ->exists()
        ) {
            return back()->withErrors([
                'location' =>
                    'Este local possui locais filhos ativos. Desative primeiro os níveis inferiores.',
            ]);
        }

        $serviceLocation->update([
            'active' =>
                ! $serviceLocation->active,
        ]);

        return redirect()
            ->route('service-locations.index')
            ->with(
                'success',
                $serviceLocation->active
                    ? 'Local ativado com sucesso.'
                    : 'Local desativado com sucesso.'
            );
    }

    /**
     * Confirma que o registro pertence
     * ao contexto ativo do usuário.
     */
    private function authorizeLocationContext(
        Request $request,
        ServiceLocation $serviceLocation
    ): void {
        $context = $this->activeContext(
            $request
        );

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho.'
            );
        }

        if (
            (int) $serviceLocation->unit_id
            !== (int) $context->unit_id
        ) {
            abort(
                403,
                'Este local pertence a outra unidade.'
            );
        }
    }

    /**
     * Verifica se trocar o pai criaria
     * um ciclo na árvore de locais.
     */
    private function wouldCreateCycle(
        ServiceLocation $location,
        ServiceLocation $candidateParent
    ): bool {
        $current = $candidateParent;

        while ($current) {
            if (
                (int) $current->id
                === (int) $location->id
            ) {
                return true;
            }

            if (! $current->parent_id) {
                break;
            }

            $current = ServiceLocation::query()
                ->whereKey(
                    $current->parent_id
                )
                ->where(
                    'unit_id',
                    $location->unit_id
                )
                ->first();
        }

        return false;
    }

    /**
     * Resolve a rede/unidade ativa.
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
                'network_id' =>
                    (int) $networkId,

                'unit_id' =>
                    (int) $unitId,
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