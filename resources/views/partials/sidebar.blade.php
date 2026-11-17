@php
    $user = auth()->user();
@endphp

<aside class="hidden lg:flex lg:w-64 xl:w-72 bg-emerald-800 text-white flex-col">

    {{-- =========================================================
         MARCA
    ========================================================== --}}
    <div class="h-20 flex items-center px-6 border-b border-emerald-700">

        <div class="flex items-center gap-3">

            <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center font-bold text-xl">
                V
            </div>

            <div>

                <h1 class="text-xl font-semibold">
                    Vitallis
                </h1>

                <p class="text-xs text-emerald-200">
                    Prontuário Eletrônico
                </p>

            </div>

        </div>

    </div>


    {{-- =========================================================
         NAVEGAÇÃO
    ========================================================== --}}
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">

        {{-- Dashboard --}}
        @if($user->hasPermission('dashboard.view'))

            <a
                href="{{ route('dashboard') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('dashboard')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Dashboard
            </a>

        @endif


        {{-- =====================================================
             ÁREA ASSISTENCIAL
        ====================================================== --}}

        {{-- Pacientes --}}
        @if($user->hasPermission('patients.view'))

            <a
                href="{{ route('patients.index') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('patients.*')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Pacientes
            </a>

        @endif


        {{-- Agenda --}}
        @if($user->hasPermission('appointments.view'))
        
            <a
                href="{{ route('appointments.index') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('appointments.*')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Agenda
            </a>
        
        @endif


        {{-- Prontuários --}}
        @if($user->hasPermission('medical_records.view'))

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
            >
                Prontuários
            </a>

        @endif


        {{-- Prescrições --}}
        @if($user->hasPermission('prescriptions.view'))

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
            >
                Prescrições
            </a>

        @endif


        {{-- Exames --}}
        @if($user->hasPermission('exams.view'))

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
            >
                Exames
            </a>

        @endif


        {{-- Relatórios --}}
        @if($user->hasPermission('reports.view'))

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
            >
                Relatórios
            </a>

        @endif


        {{-- =====================================================
             ADMINISTRAÇÃO
        ====================================================== --}}

        @if(
            $user->hasPermission('users.view') ||
            $user->hasPermission('roles.view') ||
            $user->hasPermission('permissions.view') ||
            $user->hasPermission('unit_permissions.view') ||
            $user->hasPermission('network.view') ||
            $user->hasPermission('unit.view') ||
            $user->hasPermission('audit.view')
        )

            <div class="pt-5 mt-5 border-t border-emerald-700">

                <p class="px-4 mb-2 text-xs font-medium uppercase tracking-wider text-emerald-300">
                    Administração
                </p>

            </div>

        @endif


        {{-- Usuários --}}
        @if($user->hasPermission('users.view'))

            <a
                href="{{ route('users.index') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('users.*')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Usuários
            </a>

        @endif


        {{-- Funções e Permissões --}}
        @if(
            $user->hasPermission('roles.view') ||
            $user->hasPermission('permissions.view') ||
            $user->hasPermission('unit_permissions.view')
        )

            <a
                href="{{ route('roles.index') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('roles.*')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Funções e Permissões
            </a>

        @endif


        {{-- Redes --}}
        @if($user->hasPermission('network.view'))

            <a
                href="{{ route('networks.index') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('networks.*')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Redes
            </a>

        @endif


        {{-- Unidades --}}
        @if($user->hasPermission('unit.view'))

            <a
                href="{{ route('units.index') }}"
                class="flex items-center rounded-lg px-4 py-3 text-sm transition
                    {{ request()->routeIs('units.*')
                        ? 'bg-emerald-700 font-medium text-white'
                        : 'text-emerald-50 hover:bg-emerald-700' }}"
            >
                Unidades
            </a>

        @endif


        {{-- Auditoria --}}
        @if($user->hasPermission('audit.view'))

            <a
                href="#"
                class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
            >
                Auditoria
            </a>

        @endif

    </nav>


    {{-- =========================================================
         USUÁRIO / LOGOUT
    ========================================================== --}}
    <div class="border-t border-emerald-700 p-4">

        <div class="mb-3 px-3">

            <p class="truncate text-sm font-medium text-white">
                {{ $user->name }}
            </p>

            <p class="truncate text-xs text-emerald-200">
                {{ $user->email }}
            </p>

        </div>


        <form
            method="POST"
            action="{{ route('logout') }}"
        >

            @csrf

            <button
                type="submit"
                class="w-full rounded-lg px-3 py-2 text-left text-sm text-emerald-100 transition hover:bg-emerald-700"
            >
                Sair
            </button>

        </form>

    </div>

</aside>