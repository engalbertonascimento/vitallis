@extends('layouts.app')

@section('title', 'Agenda | Vitallis')
@section('header', 'Agenda')
@section('subtitle', 'Agenda de atendimentos da unidade atual')

@section('content')

@php
    $canCreateAppointment = $user->hasPermission('appointments.create');

    $canManageSchedules =
        $user->hasPermission('schedules.create')
        || $user->hasPermission('schedules.update')
        || $user->hasPermission('schedules.block');

    $previousDate = $date->copy()->subDay()->format('Y-m-d');
    $nextDate = $date->copy()->addDay()->format('Y-m-d');

    $baseFilters = request()->only([
        'professional_id',
        'specialty_id',
        'status',
    ]);
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

    {{-- Navegação --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div class="flex items-center gap-2">

                {{-- Dia anterior --}}
                <a
                    href="{{ route('appointments.index', array_merge($baseFilters, ['date' => $previousDate])) }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50"
                    title="Dia anterior"
                >
                    ←
                </a>

                {{-- Hoje --}}
                <a
                    href="{{ route('appointments.index', array_merge($baseFilters, ['date' => now()->format('Y-m-d')])) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Hoje
                </a>

                {{-- Próximo dia --}}
                <a
                    href="{{ route('appointments.index', array_merge($baseFilters, ['date' => $nextDate])) }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50"
                    title="Próximo dia"
                >
                    →
                </a>

                {{-- Calendário --}}
                <div class="relative">

                    <button
                        type="button"
                        onclick="openAgendaCalendar()"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm transition hover:bg-slate-50"
                        title="Escolher data"
                    >
                        📅
                    </button>

                    <input
                        id="agendaDate"
                        type="date"
                        value="{{ $date->format('Y-m-d') }}"
                        class="pointer-events-none absolute inset-0 h-0 w-0 opacity-0"
                        onchange="changeAgendaDate(this.value)"
                        tabindex="-1"
                    >

                </div>

            </div>

            {{-- Data atual --}}
            <div class="text-center">

                <p class="font-semibold capitalize text-slate-900">
                    {{ $date->translatedFormat('d \d\e F \d\e Y') }}
                </p>

                <p class="mt-1 text-xs capitalize text-slate-500">
                    {{ $date->translatedFormat('l') }}
                </p>

            </div>

            {{-- Modo --}}
            <div class="flex rounded-lg border border-slate-300 bg-white p-1">

                <button
                    type="button"
                    class="rounded-md bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
                >
                    Dia
                </button>

                <button
                    type="button"
                    disabled
                    title="Visualização semanal será implementada posteriormente"
                    class="cursor-not-allowed rounded-md px-4 py-2 text-sm text-slate-400"
                >
                    Semana
                </button>

            </div>

        </div>

    </section>

    {{-- Filtros --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('appointments.index') }}"
            id="agendaFilters"
        >

            <input
                type="hidden"
                name="date"
                value="{{ $date->format('Y-m-d') }}"
            >

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- Profissional --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Profissional
                    </label>

                    <select
                        name="professional_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700"
                    >
                        <option value="">
                            Todos os profissionais
                        </option>

                        @foreach($professionals as $professional)
                            <option
                                value="{{ $professional->id }}"
                                @selected(request('professional_id') == $professional->id)
                            >
                                {{ $professional->name }}
                            </option>
                        @endforeach
                    </select>

                </div>

                {{-- Especialidade --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Especialidade
                    </label>

                    <select
                        name="specialty_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700"
                    >
                        <option value="">
                            Todas as especialidades
                        </option>

                        @foreach($specialties as $specialty)
                            <option
                                value="{{ $specialty->id }}"
                                @selected(request('specialty_id') == $specialty->id)
                            >
                                {{ $specialty->name }}
                            </option>
                        @endforeach
                    </select>

                </div>

                {{-- Situação --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Situação
                    </label>

                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700"
                    >
                        <option value="">
                            Todos os atendimentos
                        </option>

                        <option
                            value="scheduled"
                            @selected(request('status') === 'scheduled')
                        >
                            Agendado
                        </option>

                        <option
                            value="confirmed"
                            @selected(request('status') === 'confirmed')
                        >
                            Confirmado
                        </option>

                        <option
                            value="waiting"
                            @selected(request('status') === 'waiting')
                        >
                            Aguardando
                        </option>

                        <option
                            value="in_progress"
                            @selected(request('status') === 'in_progress')
                        >
                            Em atendimento
                        </option>

                        <option
                            value="completed"
                            @selected(request('status') === 'completed')
                        >
                            Finalizado
                        </option>

                        <option
                            value="cancelled"
                            @selected(request('status') === 'cancelled')
                        >
                            Cancelado
                        </option>

                        <option
                            value="no_show"
                            @selected(request('status') === 'no_show')
                        >
                            Não compareceu
                        </option>

                    </select>

                </div>

            </div>

        </form>

    </section>

    {{-- Agenda --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="font-semibold text-slate-900">
                        Agenda do dia
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Horários disponíveis e pacientes agendados.
                    </p>

                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    {{ $appointments->count() }}
                    {{ $appointments->count() === 1 ? 'atendimento' : 'atendimentos' }}
                </span>

            </div>

        </div>

        @if($schedules->isEmpty())

            <div class="px-6 py-16 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 font-semibold text-emerald-700">
                    A
                </div>

                <h3 class="mt-4 font-medium text-slate-800">
                    Nenhum horário disponível
                </h3>

                <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-slate-500">
                    Não existem grades de atendimento disponíveis para esta unidade nesta data.
                </p>

                @if($canManageSchedules)
                    <p class="mt-4 text-sm font-medium text-emerald-700">
                        Seu perfil possui permissão para administrar grades de agenda.
                    </p>
                @endif

            </div>

        @else

            <div class="divide-y divide-slate-100">

                @foreach($schedules as $schedule)

                    @php
                        $scheduleAppointments = $appointments
                            ->where('schedule_id', $schedule->id)
                            ->sortBy('starts_at');
                    @endphp

                    <div class="px-6 py-5">

                        {{-- Cabeçalho da grade --}}
                        <div class="flex items-start justify-between gap-6">

                            <div>

                                <p class="font-semibold text-slate-900">
                                    {{ $schedule->professional?->name ?? 'Profissional não identificado' }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">

                                    {{ $schedule->specialtyRelation?->name ?? 'Sem especialidade' }}

                                    @if($schedule->serviceLocation)
                                        <span class="text-slate-300">•</span>
                                        {{ $schedule->serviceLocation->name }}
                                    @endif

                                </p>

                                @if($schedule->name)
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $schedule->name }}
                                    </p>
                                @endif

                            </div>

                            <div class="whitespace-nowrap text-right">

                                <p class="text-sm font-semibold text-slate-700">
                                    {{ substr($schedule->starts_at, 0, 5) }}
                                    às
                                    {{ substr($schedule->ends_at, 0, 5) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $schedule->slot_duration_minutes }} min por horário
                                </p>

                            </div>

                        </div>

                        {{-- Pacientes da grade --}}
                        @if($scheduleAppointments->isNotEmpty())

                            <div class="mt-4 overflow-hidden rounded-lg border border-slate-200">

                                @foreach($scheduleAppointments as $appointment)

                                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 last:border-b-0 hover:bg-slate-50">

                                        {{-- Horário e paciente --}}
                                        <div class="flex min-w-0 items-center gap-4">

                                            <div class="w-14 shrink-0">

                                                <p class="font-semibold text-slate-900">
                                                    {{ $appointment->starts_at->format('H:i') }}
                                                </p>

                                                <p class="mt-0.5 text-xs text-slate-400">
                                                    {{ $appointment->ends_at->format('H:i') }}
                                                </p>

                                            </div>

                                            <div class="min-w-0">

                                                <p class="truncate text-sm font-medium text-slate-900">
                                                    {{ $appointment->patient?->name ?? 'Paciente não identificado' }}
                                                </p>

                                                @if($appointment->status === 'scheduled')

                                                    <p class="mt-0.5 text-xs font-medium text-blue-600">
                                                        Agendado
                                                    </p>

                                                @elseif($appointment->status === 'confirmed')

                                                    <p class="mt-0.5 text-xs font-medium text-indigo-600">
                                                        Confirmado
                                                    </p>

                                                @elseif($appointment->status === 'waiting')

                                                    <p class="mt-0.5 text-xs font-medium text-amber-600">
                                                        Aguardando atendimento

                                                        @if($appointment->checked_in_at)
                                                            • chegada às
                                                            {{ $appointment->checked_in_at->format('H:i') }}
                                                        @endif
                                                    </p>

                                                @elseif($appointment->status === 'in_progress')

                                                    <p class="mt-0.5 text-xs font-medium text-violet-600">
                                                        Em atendimento
                                                    </p>

                                                @elseif($appointment->status === 'completed')

                                                    <p class="mt-0.5 text-xs font-medium text-emerald-600">
                                                        Finalizado

                                                        @if($appointment->completed_at)
                                                            às
                                                            {{ $appointment->completed_at->format('H:i') }}
                                                        @endif
                                                    </p>

                                                @elseif($appointment->status === 'no_show')

                                                    <p class="mt-0.5 text-xs font-medium text-slate-500">
                                                        Não compareceu
                                                    </p>

                                                @elseif($appointment->status === 'cancelled')

                                                    <p class="mt-0.5 text-xs font-medium text-red-600">
                                                        Cancelado
                                                    </p>

                                                @else

                                                    <p class="mt-0.5 text-xs font-medium text-slate-500">
                                                        {{ $appointment->status }}
                                                    </p>

                                                @endif

                                                @if($appointment->reason)

                                                    <p
                                                        class="mt-1 max-w-xl truncate text-xs text-slate-400"
                                                        title="{{ $appointment->reason }}"
                                                    >
                                                        {{ $appointment->reason }}
                                                    </p>

                                                @endif

                                            </div>

                                        </div>

                                        {{-- Ações --}}
                                        <div class="flex shrink-0 items-center gap-2">

                                            {{-- Check-in --}}
                                            @if(
                                                $user->hasPermission('appointments.checkin')
                                                && in_array($appointment->status, ['scheduled', 'confirmed'])
                                            )

                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.checkin', $appointment) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="whitespace-nowrap rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800"
                                                    >
                                                        Paciente chegou
                                                    </button>

                                                </form>

                                            @endif

                                            {{-- Ausência --}}
                                            @if(
                                                $user->hasPermission('appointments.no_show')
                                                && in_array($appointment->status, ['scheduled', 'confirmed'])
                                                && now()->gte($appointment->starts_at)
                                            )

                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.no_show', $appointment) }}"
                                                    onsubmit="return confirm('Confirmar que o paciente não compareceu?')"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="whitespace-nowrap rounded-lg border border-red-300 bg-white px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50"
                                                    >
                                                        Não compareceu
                                                    </button>

                                                </form>

                                            @endif

                                            {{-- Iniciar atendimento --}}
                                            @if(
                                                $user->hasPermission('appointments.start')
                                                && $appointment->status === 'waiting'
                                            )

                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.start', $appointment) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="whitespace-nowrap rounded-lg bg-violet-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-800"
                                                    >
                                                        Iniciar atendimento
                                                    </button>

                                                </form>

                                            @endif

                                            {{-- Finalizar atendimento --}}
                                            @if(
                                                $user->hasPermission('appointments.complete')
                                                && $appointment->status === 'in_progress'
                                            )

                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.complete', $appointment) }}"
                                                    onsubmit="return confirm('Confirmar a finalização deste atendimento?')"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="whitespace-nowrap rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800"
                                                    >
                                                        Finalizar atendimento
                                                    </button>

                                                </form>

                                            @endif
                                            
                                            {{-- Confirmar agendamento --}}
                                            @if(
                                                $user->hasPermission('appointments.confirm')
                                                && $appointment->status === 'scheduled'
                                            )
                                            
                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.confirm', $appointment) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                            
                                                    <button
                                                        type="submit"
                                                        class="whitespace-nowrap rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                                                    >
                                                        Confirmar
                                                    </button>
                                                </form>
                                            
                                            @endif
                                            
                                            {{-- Reagendar --}}
                                            @if(
                                                $user->hasPermission('appointments.reschedule')
                                                && in_array($appointment->status, ['scheduled', 'confirmed'])
                                            )
                                            
                                                <a
                                                    href="{{ route('appointments.reschedule', $appointment) }}"
                                                    class="whitespace-nowrap rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-50"
                                                >
                                                    Reagendar
                                                </a>
                                            
                                            @endif
                                            
                                            {{-- Cancelar --}}
                                            @if(
                                                $user->hasPermission('appointments.cancel')
                                                && in_array($appointment->status, ['scheduled', 'confirmed'])
                                            )
                                            
                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.cancel', $appointment) }}"
                                                    onsubmit="return confirm('Confirma o cancelamento deste agendamento?')"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                            
                                                    <button
                                                        type="submit"
                                                        class="whitespace-nowrap rounded-lg border border-red-300 bg-white px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50"
                                                    >
                                                        Cancelar
                                                    </button>
                                                </form>
                                            
                                            @endif

                                            {{-- Estados finais --}}
                                            @if($appointment->status === 'completed')

                                                <span class="whitespace-nowrap text-xs font-semibold text-emerald-700">
                                                    ✓ Concluído
                                                </span>

                                            @elseif($appointment->status === 'no_show')

                                                <span class="whitespace-nowrap text-xs font-medium text-slate-500">
                                                    Paciente ausente
                                                </span>

                                            @elseif($appointment->status === 'cancelled')

                                                <span class="whitespace-nowrap text-xs font-medium text-red-600">
                                                    Cancelado
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        @else

                            <div class="mt-4 rounded-lg border border-dashed border-slate-200 px-4 py-4">

                                <p class="text-sm text-slate-400">
                                    Nenhum paciente agendado nesta grade.
                                </p>

                            </div>

                        @endif

                    </div>

                @endforeach

            </div>

        @endif

    </section>

</div>

<script>
function openAgendaCalendar() {
    const input = document.getElementById('agendaDate');

    if (typeof input.showPicker === 'function') {
        input.showPicker();
        return;
    }

    input.click();
}

function changeAgendaDate(date) {
    if (!date) {
        return;
    }

    const url = new URL(window.location.href);

    url.searchParams.set('date', date);

    window.location.href = url.toString();
}
</script>

@endsection
