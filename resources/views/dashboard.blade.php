@extends('layouts.app')

@section('title', 'Dashboard | Vitallis')
@section('header', 'Dashboard')
@section('subtitle', 'Visão geral do sistema')

@section('content')

{{-- Boas-vindas --}}
<div class="mb-8">

    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">
        Olá, {{ $user->name }}
    </h2>

    <p class="mt-1 text-slate-500">
        Bem-vindo ao Vitallis. Confira abaixo as informações disponíveis para o seu perfil.
    </p>

</div>


{{-- Cards --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

    {{-- Pacientes --}}
    @if($user->hasPermission('patients.view'))

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Pacientes
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        -
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 font-semibold text-emerald-700">
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

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Atendimentos hoje
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        -
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 font-semibold text-blue-700">
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

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Unidades
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        -
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 font-semibold text-violet-700">
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

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Usuários
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        -
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 font-semibold text-amber-700">
                    U
                </div>

            </div>

            <p class="mt-4 text-xs text-slate-400">
                Usuários disponíveis no contexto atual
            </p>

        </div>

    @endif

</div>


{{-- Conteúdo inferior --}}
<div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">

    {{-- Atividade --}}
    <section class="rounded-xl border border-slate-200 bg-white xl:col-span-2">

        <div class="border-b border-slate-200 px-6 py-5">

            <h3 class="font-semibold text-slate-900">
                Atividade recente
            </h3>

            <p class="mt-1 text-sm text-slate-500">
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
    <section class="rounded-xl border border-slate-200 bg-white">

        <div class="border-b border-slate-200 px-6 py-5">

            <h3 class="font-semibold text-slate-900">
                Contexto de acesso
            </h3>

        </div>

        <div class="space-y-5 p-6">

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

            @else

                @php
                    $activeRole = $user->roles()
                        ->wherePivot('active', true)
                        ->first();
                @endphp

                @if($activeRole)

                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Perfil
                        </p>

                        <div class="mt-2">

                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                {{ $activeRole->name }}
                            </span>

                        </div>

                    </div>

                @endif

            @endif

        </div>

    </section>

</div>

@endsection