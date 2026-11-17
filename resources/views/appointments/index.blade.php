@extends('layouts.app')

@section('title', 'Agenda | Vitallis')
@section('header', 'Agenda')
@section('subtitle', 'Agenda de atendimentos da unidade atual')

@section('content')

@php
    $canCreateAppointment =
        $user->hasPermission('appointments.create');

    $canManageSchedules =
        $user->hasPermission('schedules.create')
        || $user->hasPermission('schedules.update')
        || $user->hasPermission('schedules.block');
@endphp

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <h1 class="text-2xl font-semibold text-slate-900">
                Agenda
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Visualize os horários e atendimentos disponíveis na unidade atual.
            </p>

        </div>


        <div class="flex flex-wrap gap-3">

            @if($user->hasPermission('schedules.view'))
            
                <a
                    href="{{ route('schedules.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                >
                    Gerenciar grade
                </a>
            
            @endif


            @if($canCreateAppointment)
            
                <a
                    href="{{ route('appointments.create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                >
                    Novo agendamento
                </a>
            
            @endif

        </div>

    </div>


    {{-- Navegação de período --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div class="flex items-center gap-2">

                <button
                    type="button"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600"
                >
                    ←
                </button>

                <button
                    type="button"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                >
                    Hoje
                </button>

                <button
                    type="button"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600"
                >
                    →
                </button>

            </div>


            <div>

                <p class="font-semibold text-slate-900">
                    {{ now()->translatedFormat('F \d\e Y') }}
                </p>

            </div>


            <div class="flex rounded-lg border border-slate-300 bg-white p-1">

                <button
                    type="button"
                    class="rounded-md bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
                >
                    Dia
                </button>

                <button
                    type="button"
                    class="rounded-md px-4 py-2 text-sm text-slate-600"
                >
                    Semana
                </button>

            </div>

        </div>

    </section>


    {{-- Filtros --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Profissional
                </label>

                <select
                    disabled
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500"
                >
                    <option>Todos os profissionais</option>
                </select>
            </div>


            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Especialidade
                </label>

                <select
                    disabled
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500"
                >
                    <option>Todas as especialidades</option>
                </select>
            </div>


            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Situação
                </label>

                <select
                    disabled
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500"
                >
                    <option>Todos os atendimentos</option>
                </select>
            </div>

        </div>

    </section>


    {{-- Agenda vazia --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="font-semibold text-slate-900">
                        Agenda do dia
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Horários disponíveis e pacientes agendados aparecerão aqui.
                    </p>

                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    0 atendimentos
                </span>

            </div>

        </div>


        <div class="px-6 py-16 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 font-semibold text-emerald-700">
                A
            </div>

            <h3 class="mt-4 font-medium text-slate-800">
                Nenhum horário disponível
            </h3>

            <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-slate-500">
                Ainda não existem grades de atendimento cadastradas
                para esta unidade e período.
            </p>


            @if($canManageSchedules)

                <p class="mt-4 text-sm font-medium text-emerald-700">
                    Seu perfil possui permissão para administrar grades de agenda.
                </p>

            @elseif($canCreateAppointment)

                <p class="mt-4 text-sm text-slate-500">
                    Você poderá criar agendamentos quando houver horários disponíveis.
                </p>

            @else

                <p class="mt-4 text-sm text-slate-500">
                    Sua função possui acesso somente à visualização da agenda.
                </p>

            @endif

        </div>

    </section>


    {{-- Legenda futura --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5">

        <h2 class="text-sm font-semibold text-slate-800">
            Estados do atendimento
        </h2>

        <div class="mt-4 flex flex-wrap gap-3">

            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                Agendado
            </span>

            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                Confirmado
            </span>

            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                Aguardando
            </span>

            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700">
                Em atendimento
            </span>

            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                Finalizado
            </span>

            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">
                Cancelado
            </span>

            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                Não compareceu
            </span>

        </div>

    </section>

</div>

@endsection
