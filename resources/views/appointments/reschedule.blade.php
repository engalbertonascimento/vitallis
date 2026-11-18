@extends('layouts.app')

@section('title', 'Reagendar atendimento | Vitallis')
@section('header', 'Reagendar atendimento')
@section('subtitle', 'Selecione uma nova data e horário')

@section('content')

<div class="mx-auto max-w-4xl space-y-6">

    <div>
        <a
            href="{{ route('appointments.index', ['date' => $appointment->starts_at->format('Y-m-d')]) }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para agenda
        </a>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <h2 class="font-semibold text-slate-900">
            Agendamento atual
        </h2>

        <div class="mt-4 grid grid-cols-2 gap-4">

            <div>
                <p class="text-xs uppercase text-slate-400">Paciente</p>
                <p class="mt-1 font-medium text-slate-900">
                    {{ $appointment->patient?->name }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase text-slate-400">Profissional</p>
                <p class="mt-1 font-medium text-slate-900">
                    {{ $appointment->professional?->name }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase text-slate-400">Data atual</p>
                <p class="mt-1 text-slate-700">
                    {{ $appointment->starts_at->format('d/m/Y') }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase text-slate-400">Horário atual</p>
                <p class="mt-1 text-slate-700">
                    {{ $appointment->starts_at->format('H:i') }}
                </p>
            </div>

        </div>

    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <h2 class="font-semibold text-slate-900">
            Novo horário
        </h2>

        <form
            method="POST"
            action="{{ route('appointments.reschedule.update', $appointment) }}"
            class="mt-5 space-y-5"
        >
            @csrf
            @method('PATCH')

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Grade
                </label>

                <select
                    name="schedule_id"
                    required
                    class="w-full rounded-lg border border-slate-300 px-4 py-3"
                >
                    <option value="">Selecione</option>

                    @foreach($schedules as $schedule)
                        <option
                            value="{{ $schedule->id }}"
                            @selected(old('schedule_id', $appointment->schedule_id) == $schedule->id)
                        >
                            {{ $schedule->professional?->name }}
                            -
                            {{ $schedule->specialtyRelation?->name ?? 'Sem especialidade' }}
                            -
                            {{ substr($schedule->starts_at, 0, 5) }}
                            às
                            {{ substr($schedule->ends_at, 0, 5) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Nova data
                    </label>

                    <input
                        type="date"
                        name="date"
                        value="{{ old('date', $appointment->starts_at->format('Y-m-d')) }}"
                        min="{{ now()->format('Y-m-d') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-4 py-3"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Novo horário
                    </label>

                    <input
                        type="time"
                        name="time"
                        value="{{ old('time', $appointment->starts_at->format('H:i')) }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-4 py-3"
                    >
                </div>

            </div>

            @if($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('appointments.index', ['date' => $appointment->starts_at->format('Y-m-d')]) }}"
                    class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
                >
                    Confirmar reagendamento
                </button>

            </div>

        </form>

    </section>

</div>

@endsection
