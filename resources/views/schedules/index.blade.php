@extends('layouts.app')

@section('title', 'Grades de Agenda | Vitallis')
@section('header', 'Grades de Agenda')
@section('subtitle', 'Configuração dos horários disponíveis para atendimento')

@section('content')

@php
    $weekdayNames = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];
@endphp

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <a
                href="{{ route('appointments.index') }}"
                class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
            >
                ← Voltar para agenda
            </a>

            <h1 class="mt-4 text-2xl font-semibold text-slate-900">
                Grades de Agenda
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Configure os períodos em que os profissionais estarão disponíveis para agendamento.
            </p>

        </div>


        @if(auth()->user()->hasPermission('schedules.create'))

            <a
                href="{{ route('schedules.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Nova grade
            </a>

        @endif

    </div>


    {{-- Unidade --}}
    <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">

        <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
            Unidade
        </p>

        <p class="mt-1 font-semibold text-emerald-900">
            {{ $unit->name }}
        </p>

        @if($unit->network)

            <p class="mt-1 text-sm text-emerald-700">
                {{ $unit->network->name }}
            </p>

        @endif

    </section>


    {{-- Lista --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Grades cadastradas
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $schedules->count() }}
                {{ $schedules->count() === 1 ? 'grade cadastrada' : 'grades cadastradas' }}
                nesta unidade.
            </p>

        </div>


        @if($schedules->isEmpty())

            <div class="px-6 py-16 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 font-semibold text-emerald-700">
                    A
                </div>

                <h3 class="mt-4 font-medium text-slate-800">
                    Nenhuma grade cadastrada
                </h3>

                <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">
                    Ainda não existem horários disponibilizados para atendimento nesta unidade.
                </p>


                @if(auth()->user()->hasPermission('schedules.create'))

                    <a
                        href="{{ route('schedules.create') }}"
                        class="mt-5 inline-flex rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Criar primeira grade
                    </a>

                @endif

            </div>

        @else

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Profissional
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Dia
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Horário
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Intervalo
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Validade
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($schedules as $schedule)

                            <tr class="hover:bg-slate-50">

                                {{-- Profissional --}}
                                <td class="px-6 py-4">

                                    <p class="font-medium text-slate-900">
                                        {{ $schedule->professional?->name ?? 'Não identificado' }}
                                    </p>

                                    @if($schedule->specialty)

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $schedule->specialty }}
                                        </p>

                                    @endif

                                    @if($schedule->name)

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $schedule->name }}
                                        </p>

                                    @endif

                                </td>


                                {{-- Dia --}}
                                <td class="px-6 py-4 text-sm text-slate-700">

                                    {{ $weekdayNames[$schedule->weekday] ?? 'Não informado' }}

                                </td>


                                {{-- Horário --}}
                                <td class="px-6 py-4">

                                    <p class="text-sm font-medium text-slate-700">
                                        {{ substr($schedule->starts_at, 0, 5) }}
                                        às
                                        {{ substr($schedule->ends_at, 0, 5) }}
                                    </p>

                                    @if($schedule->room)

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $schedule->room }}
                                        </p>

                                    @endif

                                </td>


                                {{-- Intervalo --}}
                                <td class="px-6 py-4">

                                    <p class="text-sm text-slate-700">
                                        {{ $schedule->slot_duration_minutes }} min
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Capacidade:
                                        {{ $schedule->slot_capacity }}
                                    </p>

                                </td>


                                {{-- Validade --}}
                                <td class="px-6 py-4 text-sm text-slate-700">

                                    <p>
                                        {{ $schedule->starts_on->format('d/m/Y') }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">

                                        @if($schedule->ends_on)
                                            até {{ $schedule->ends_on->format('d/m/Y') }}
                                        @else
                                            sem data final
                                        @endif

                                    </p>

                                </td>


                                {{-- Status --}}
                                <td class="px-6 py-4">

                                    @if($schedule->active)

                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Ativa
                                        </span>

                                    @else

                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            Inativa
                                        </span>

                                    @endif

                                </td>


                                {{-- Ações --}}
                                <td class="px-6 py-4 text-right">

                                    @if(auth()->user()->hasPermission('schedules.update'))

                                        <a
                                            href="{{ route('schedules.edit', $schedule) }}"
                                            class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            Somente leitura
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </section>

</div>

@endsection
