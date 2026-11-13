<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | Vitallis</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-900">

<div class="min-h-screen flex">

    {{-- ============================================================
         MENU LATERAL
    ============================================================= --}}
    <aside class="hidden lg:flex lg:w-64 xl:w-72 bg-emerald-800 text-white flex-col">

        {{-- Marca --}}
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


        {{-- Navegação --}}
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">

            {{-- Dashboard --}}
            @if($user->hasPermission('dashboard.view'))

                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center rounded-lg bg-emerald-700 px-4 py-3 text-sm font-medium text-white"
                >
                    Dashboard
                </a>

            @endif


            {{-- Pacientes --}}
            @if($user->hasPermission('patients.view'))

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
                >
                    Pacientes
                </a>

            @endif


            {{-- Agenda --}}
            @if($user->hasPermission('appointments.view'))

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
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


            {{-- Administração --}}
            @if(
                $user->hasPermission('users.view') ||
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
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
                >
                    Usuários
                </a>

            @endif


            {{-- Redes --}}
            @if($user->hasPermission('network.view'))

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
                >
                    Redes
                </a>

            @endif


            {{-- Unidades --}}
            @if($user->hasPermission('unit.view'))

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm text-emerald-50 transition hover:bg-emerald-700"
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


        {{-- Usuário / logout --}}
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


    {{-- ============================================================
         ÁREA PRINCIPAL
    ============================================================= --}}
    <main class="flex-1 min-w-0">

        {{-- Cabeçalho --}}
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8">

            <div>

                <h2 class="font-semibold text-slate-900">
                    Dashboard
                </h2>

                <p class="text-sm text-slate-500">
                    Visão geral do sistema
                </p>

            </div>


            {{-- Usuário --}}
            <div class="flex items-center gap-3">

                <div class="hidden sm:block text-right">

                    <p class="text-sm font-medium text-slate-900">
                        {{ $user->name }}
                    </p>

                    <p class="text-xs text-slate-500">
                        {{ $user->email }}
                    </p>

                </div>

                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold">

                    {{ strtoupper(substr($user->name, 0, 1)) }}

                </div>

            </div>

        </header>


        {{-- ========================================================
             CONTEÚDO
        ========================================================= --}}
        <div class="p-6 lg:p-8">

            {{-- Boas-vindas --}}
            <div class="mb-8">

                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">
                    Olá, {{ $user->name }}
                </h2>

                <p class="mt-1 text-slate-500">
                    Bem-vindo ao Vitallis. Confira abaixo as informações disponíveis para o seu perfil.
                </p>

            </div>


            {{-- ====================================================
                 CARDS
            ===================================================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">


                {{-- Pacientes --}}
                @if($user->hasPermission('patients.view'))

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Pacientes
                                </p>

                                <p class="mt-3 text-3xl font-semibold text-slate-900">
                                    -
                                </p>

                            </div>

                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-semibold">
                                P
                            </div>

                        </div>

                        <p class="mt-4 text-xs text-slate-400">
                            Pacientes acessíveis no contexto atual
                        </p>

                    </div>

                @endif


                {{-- Atendimentos --}}
                @if($user->hasPermission('appointments.view'))

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Atendimentos hoje
                                </p>

                                <p class="mt-3 text-3xl font-semibold text-slate-900">
                                    -
                                </p>

                            </div>

                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-semibold">
                                A
                            </div>

                        </div>

                        <p class="mt-4 text-xs text-slate-400">
                            Atendimentos previstos para hoje
                        </p>

                    </div>

                @endif


                {{-- Unidades --}}
                @if($user->hasPermission('unit.view'))

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Unidades
                                </p>

                                <p class="mt-3 text-3xl font-semibold text-slate-900">
                                    -
                                </p>

                            </div>

                            <div class="w-10 h-10 rounded-lg bg-violet-50 text-violet-700 flex items-center justify-center font-semibold">
                                U
                            </div>

                        </div>

                        <p class="mt-4 text-xs text-slate-400">
                            Unidades disponíveis para o usuário
                        </p>

                    </div>

                @endif


                {{-- Usuários --}}
                @if($user->hasPermission('users.view'))

                    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">

                        <div class="flex items-start justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500">
                                    Usuários
                                </p>

                                <p class="mt-3 text-3xl font-semibold text-slate-900">
                                    -
                                </p>

                            </div>

                            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-semibold">
                                U
                            </div>

                        </div>

                        <p class="mt-4 text-xs text-slate-400">
                            Usuários disponíveis no contexto atual
                        </p>

                    </div>

                @endif

            </div>


            {{-- ====================================================
                 CONTEÚDO INFERIOR
            ===================================================== --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-8">

                {{-- Atividade --}}
                <section class="xl:col-span-2 bg-white border border-slate-200 rounded-xl">

                    <div class="px-6 py-5 border-b border-slate-200">

                        <h3 class="font-semibold text-slate-900">
                            Atividade recente
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Eventos relevantes aparecerão aqui.
                        </p>

                    </div>

                    <div class="p-8 text-center">

                        <p class="text-sm text-slate-400">
                            Nenhuma atividade disponível.
                        </p>

                    </div>

                </section>


                {{-- Contexto --}}
                <section class="bg-white border border-slate-200 rounded-xl">

                    <div class="px-6 py-5 border-b border-slate-200">

                        <h3 class="font-semibold text-slate-900">
                            Contexto de acesso
                        </h3>

                    </div>

                    <div class="p-6 space-y-5">

                        <div>

                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Usuário
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-900">
                                {{ $user->name }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Ambiente
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-900">
                                Vitallis
                            </p>

                        </div>


                        @if($user->isVitallisAdmin())

                            <div>

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Perfil
                                </p>

                                <div class="mt-2">

                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Administrador Vitallis
                                    </span>

                                </div>

                            </div>

                        @endif

                    </div>

                </section>

            </div>

        </div>

    </main>

</div>

</body>
</html>
