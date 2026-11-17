@php
    $user = auth()->user();

    /*
     * Contexto atualmente selecionado pelo usuário.
     */
    $currentContext = \Illuminate\Support\Facades\DB::table('user_contexts')
        ->where('user_id', $user->id)
        ->first();

    $currentNetwork = null;
    $currentUnit = null;
    $currentRole = null;

    /*
     * Unidades disponíveis para troca de contexto.
     */
    $availableUnits = collect();

    /*
     * Resolve rede, unidade e função do contexto atual.
     */
    if ($currentContext) {
        if ($currentContext->network_id) {
            $currentNetwork = \App\Models\Network::find(
                $currentContext->network_id
            );
        }

        if ($currentContext->unit_id) {
            $currentUnit = \App\Models\Unit::find(
                $currentContext->unit_id
            );
        }

        /*
         * Procura diretamente em role_user:
         *
         * 1. vínculo ativo do usuário;
         * 2. mesma rede do contexto;
         * 3. vínculo específico da unidade ou de toda a rede;
         * 4. prioriza o vínculo específico da unidade.
         */
        $roleLinkQuery = \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('active', true);

        if ($currentContext->network_id) {
            $roleLinkQuery->where(
                'network_id',
                $currentContext->network_id
            );
        }

        if ($currentContext->unit_id) {
            $roleLinkQuery->where(
                function ($query) use ($currentContext) {
                    $query
                        ->where(
                            'unit_id',
                            $currentContext->unit_id
                        )
                        ->orWhereNull('unit_id');
                }
            );

            /*
             * Um vínculo específico da unidade tem precedência
             * sobre um vínculo válido para toda a rede.
             */
            $roleLinkQuery->orderByRaw(
                'CASE WHEN unit_id = ? THEN 0 ELSE 1 END',
                [$currentContext->unit_id]
            );
        }

        $roleLink = $roleLinkQuery->first();

        if ($roleLink) {
            $currentRole = \App\Models\Role::find(
                $roleLink->role_id
            );
        }
    }

    /*
     * Administrador global.
     *
     * A consulta é feita diretamente nas tabelas para evitar
     * problemas com wherePivot() dentro de callbacks.
     */
    if ($user->isVitallisAdmin()) {
        $vitallisAdminRole = \Illuminate\Support\Facades\DB::table('role_user')
            ->join(
                'roles',
                'roles.id',
                '=',
                'role_user.role_id'
            )
            ->where(
                'role_user.user_id',
                $user->id
            )
            ->where(
                'role_user.active',
                true
            )
            ->where(
                'roles.slug',
                'vitallis-admin'
            )
            ->whereNull(
                'roles.deleted_at'
            )
            ->select([
                'roles.id',
            ])
            ->first();

        if ($vitallisAdminRole) {
            $currentRole = \App\Models\Role::find(
                $vitallisAdminRole->id
            );
        }
    }

    /*
     * Para usuários operacionais, determina quais unidades
     * estão disponíveis conforme os vínculos ativos em role_user.
     *
     * unit_id preenchido:
     * acesso somente àquela unidade.
     *
     * unit_id NULL:
     * acesso a todas as unidades da rede.
     */
    if (! $user->isVitallisAdmin()) {
        $roleLinks = \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('active', true)
            ->whereNotNull('network_id')
            ->get([
                'network_id',
                'unit_id',
            ]);

        /*
         * Redes cujo papel vale para todas as unidades.
         */
        $networkWideIds = $roleLinks
            ->whereNull('unit_id')
            ->pluck('network_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
         * Unidades explicitamente vinculadas ao usuário.
         */
        $specificUnitIds = $roleLinks
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $availableUnits = \App\Models\Unit::query()
            ->with('network')
            ->where('active', true)
            ->whereHas(
                'network',
                fn ($query) => $query->where(
                    'active',
                    true
                )
            )
            ->where(
                function ($query) use (
                    $networkWideIds,
                    $specificUnitIds
                ) {
                    if ($networkWideIds->isNotEmpty()) {
                        $query->whereIn(
                            'network_id',
                            $networkWideIds
                        );
                    }

                    if ($specificUnitIds->isNotEmpty()) {
                        if ($networkWideIds->isNotEmpty()) {
                            $query->orWhereIn(
                                'id',
                                $specificUnitIds
                            );
                        } else {
                            $query->whereIn(
                                'id',
                                $specificUnitIds
                            );
                        }
                    }

                    /*
                     * Sem vínculo ativo, não retorna nenhuma unidade.
                     */
                    if (
                        $networkWideIds->isEmpty()
                        && $specificUnitIds->isEmpty()
                    ) {
                        $query->whereRaw('1 = 0');
                    }
                }
            )
            ->orderBy('name')
            ->get();
    }
@endphp

<header
    class="min-h-20 border-b border-slate-200 bg-white px-6 lg:px-8"
    data-context-header
>

    <div class="flex min-h-20 items-center justify-between gap-6">

        {{-- =====================================================
             TÍTULO
        ====================================================== --}}
        <div class="min-w-0">

            <h2 class="font-semibold text-slate-900">
                @yield('header', 'Vitallis')
            </h2>

            @hasSection('subtitle')
                <p class="text-sm text-slate-500">
                    @yield('subtitle')
                </p>
            @endif

        </div>


        {{-- =====================================================
             CONTEXTO + USUÁRIO
        ====================================================== --}}
        <div class="flex items-center gap-4 lg:gap-6">


            {{-- =================================================
                 CONTEXTO OPERACIONAL
            ================================================== --}}
            @if(!$user->isVitallisAdmin())

                <div class="relative hidden md:block">

                    {{-- Há unidade selecionada --}}
                    @if($currentUnit)

                        <button
                            type="button"
                            id="context-dropdown-button"
                            class="group flex items-center gap-2 rounded-lg px-3 py-2 text-left transition hover:bg-slate-50"
                            aria-expanded="false"
                            aria-controls="context-dropdown"
                        >

                            <div>

                                <div class="flex items-center gap-2">

                                    <p class="text-sm font-semibold text-slate-900">

                                        {{ $currentUnit->name }}

                                        @if($currentUnit->city)
                                            - {{ $currentUnit->city }}
                                        @endif

                                    </p>

                                    <span
                                        id="context-dropdown-icon"
                                        class="text-xs text-slate-400 transition group-hover:text-slate-600"
                                    >
                                        ▼
                                    </span>

                                </div>

                                @if($currentNetwork)

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $currentNetwork->name }}
                                    </p>

                                @endif

                            </div>

                        </button>


                        {{-- Dropdown --}}
                        <div
                            id="context-dropdown"
                            class="absolute right-0 z-50 mt-2 hidden w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                        >

                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">

                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                    Unidade de trabalho
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    Selecione o contexto operacional.
                                </p>

                            </div>


                            <div class="max-h-96 overflow-y-auto">

                                @forelse(
                                    $availableUnits->groupBy('network_id')
                                    as $networkUnits
                                )

                                    @php
                                        $network = $networkUnits
                                            ->first()
                                            ->network;
                                    @endphp


                                    {{-- Rede --}}
                                    <div class="border-b border-slate-100">

                                        <div class="bg-slate-50/70 px-4 py-2">

                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                {{ $network->name }}
                                            </p>

                                        </div>


                                        {{-- Unidades --}}
                                        @foreach($networkUnits as $unit)

                                            @php
                                                $isCurrent =
                                                    $currentUnit
                                                    && (int) $currentUnit->id
                                                        === (int) $unit->id;
                                            @endphp


                                            @if($isCurrent)

                                                <div class="flex items-center justify-between bg-emerald-50 px-4 py-3">

                                                    <div>

                                                        <p class="text-sm font-medium text-emerald-900">
                                                            {{ $unit->name }}
                                                        </p>

                                                        @if($unit->city || $unit->state)

                                                            <p class="mt-0.5 text-xs text-emerald-700">

                                                                @if($unit->city)
                                                                    {{ $unit->city }}
                                                                @endif

                                                                @if($unit->city && $unit->state)
                                                                    /
                                                                @endif

                                                                @if($unit->state)
                                                                    {{ $unit->state }}
                                                                @endif

                                                            </p>

                                                        @endif

                                                    </div>

                                                    <span class="text-sm font-semibold text-emerald-700">
                                                        ✓
                                                    </span>

                                                </div>

                                            @else

                                                <form
                                                    method="POST"
                                                    action="{{ route('context.update') }}"
                                                >

                                                    @csrf
                                                    @method('PUT')

                                                    <input
                                                        type="hidden"
                                                        name="unit_id"
                                                        value="{{ $unit->id }}"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="flex w-full items-center justify-between px-4 py-3 text-left transition hover:bg-slate-50"
                                                    >

                                                        <div>

                                                            <p class="text-sm font-medium text-slate-800">
                                                                {{ $unit->name }}
                                                            </p>

                                                            @if($unit->city || $unit->state)

                                                                <p class="mt-0.5 text-xs text-slate-500">

                                                                    @if($unit->city)
                                                                        {{ $unit->city }}
                                                                    @endif

                                                                    @if($unit->city && $unit->state)
                                                                        /
                                                                    @endif

                                                                    @if($unit->state)
                                                                        {{ $unit->state }}
                                                                    @endif

                                                                </p>

                                                            @endif

                                                        </div>

                                                        <span class="text-slate-300">
                                                            →
                                                        </span>

                                                    </button>

                                                </form>

                                            @endif

                                        @endforeach

                                    </div>

                                @empty

                                    <div class="px-4 py-6 text-center">

                                        <p class="text-sm text-slate-500">
                                            Nenhuma unidade disponível.
                                        </p>

                                    </div>

                                @endforelse

                            </div>


                            {{-- Footer --}}
                            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">

                                <a
                                    href="{{ route('context.index') }}"
                                    class="text-xs font-medium text-emerald-700 hover:text-emerald-800"
                                >
                                    Ver todos os contextos
                                </a>

                            </div>

                        </div>


                    {{-- Tem rede mas ainda não unidade --}}
                    @elseif($currentNetwork)

                        <a
                            href="{{ route('context.index') }}"
                            class="block rounded-lg px-3 py-2 transition hover:bg-amber-50"
                        >

                            <p class="text-sm font-semibold text-slate-900">
                                {{ $currentNetwork->name }}
                            </p>

                            <p class="mt-0.5 text-xs font-medium text-amber-600">
                                Selecionar unidade
                            </p>

                        </a>


                    {{-- Nenhum contexto --}}
                    @else

                        <a
                            href="{{ route('context.index') }}"
                            class="block rounded-lg px-3 py-2 transition hover:bg-amber-50"
                        >

                            <p class="text-sm font-medium text-amber-700">
                                Selecionar unidade
                            </p>

                            <p class="mt-0.5 text-xs text-slate-400">
                                Defina seu contexto de trabalho
                            </p>

                        </a>

                    @endif

                </div>


                {{-- Separador --}}
                <div class="hidden h-9 w-px bg-slate-200 md:block"></div>

            @endif


            {{-- =================================================
                 USUÁRIO
            ================================================== --}}
            <div class="flex items-center gap-3">

                <div class="hidden text-right sm:block">

                    <p class="text-sm font-medium text-slate-900">
                        {{ $user->name }}
                    </p>

                    <p class="text-xs text-slate-500">

                        @if($currentRole)

                            {{ $currentRole->name }}

                        @elseif($user->isVitallisAdmin())

                            Administrador Vitallis

                        @else

                            Perfil não identificado

                        @endif

                    </p>

                </div>


                {{-- Avatar --}}
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700"
                    title="{{ $user->email }}"
                >
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

            </div>

        </div>

    </div>

</header>


{{-- =========================================================
     DROPDOWN DO CONTEXTO
========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const header = document.querySelector(
        '[data-context-header]'
    );

    if (!header) {
        return;
    }

    const button = header.querySelector(
        '#context-dropdown-button'
    );

    const dropdown = header.querySelector(
        '#context-dropdown'
    );

    const icon = header.querySelector(
        '#context-dropdown-icon'
    );

    if (!button || !dropdown) {
        return;
    }


    function openDropdown() {
        dropdown.classList.remove('hidden');

        button.setAttribute(
            'aria-expanded',
            'true'
        );

        if (icon) {
            icon.textContent = '▲';
        }
    }


    function closeDropdown() {
        dropdown.classList.add('hidden');

        button.setAttribute(
            'aria-expanded',
            'false'
        );

        if (icon) {
            icon.textContent = '▼';
        }
    }


    function toggleDropdown() {
        const isHidden =
            dropdown.classList.contains('hidden');

        if (isHidden) {
            openDropdown();
        } else {
            closeDropdown();
        }
    }


    button.addEventListener(
        'click',
        function (event) {
            event.stopPropagation();
            toggleDropdown();
        }
    );


    dropdown.addEventListener(
        'click',
        function (event) {
            event.stopPropagation();
        }
    );


    document.addEventListener(
        'click',
        function () {
            closeDropdown();
        }
    );


    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeDropdown();
            }
        }
    );

});
</script>