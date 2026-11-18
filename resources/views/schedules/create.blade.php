@extends('layouts.app')

@section('title', 'Nova Grade | Vitallis')
@section('header', 'Nova grade')
@section('subtitle', 'Configuração de disponibilidade para atendimento')

@section('content')

<div class="mx-auto max-w-5xl space-y-6">

    {{-- Cabeçalho --}}
    <div>

        <a
            href="{{ route('schedules.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para grades
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Nova grade de agenda
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Defina quando e onde o profissional estará disponível para atendimento.
        </p>

    </div>


    {{-- Erros --}}
    @if($errors->any())

        <div class="rounded-xl border border-red-200 bg-red-50 p-5">

            <p class="font-medium text-red-800">
                Verifique os dados informados.
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


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

        <p class="mt-3 text-xs text-emerald-700">
            A grade será criada exclusivamente para a unidade ativa.
        </p>

    </section>


    {{-- Dependências --}}
    @if($professionals->isEmpty() || $specialties->isEmpty() || $serviceLocations->isEmpty())

        <section class="rounded-xl border border-amber-200 bg-amber-50 p-6">

            <h2 class="font-semibold text-amber-900">
                Cadastros necessários
            </h2>

            <p class="mt-2 text-sm text-amber-700">
                Antes de criar uma grade, verifique os itens abaixo.
            </p>

            <div class="mt-4 space-y-2 text-sm">

                <p class="{{ $professionals->isEmpty() ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ $professionals->isEmpty() ? '✕' : '✓' }}
                    Profissional vinculado à unidade
                </p>

                <p class="{{ $specialties->isEmpty() ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ $specialties->isEmpty() ? '✕' : '✓' }}
                    Especialidade ativa
                </p>

                <p class="{{ $serviceLocations->isEmpty() ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ $serviceLocations->isEmpty() ? '✕' : '✓' }}
                    Local de atendimento ativo
                </p>

            </div>


            <div class="mt-5 flex flex-wrap gap-3">

                @if($specialties->isEmpty() && auth()->user()->hasPermission('specialties.manage'))

                    <a
                        href="{{ route('specialties.create') }}"
                        class="rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-medium text-amber-800"
                    >
                        Cadastrar especialidade
                    </a>

                @endif


                @if($serviceLocations->isEmpty() && auth()->user()->hasPermission('service_locations.manage'))

                    <a
                        href="{{ route('service-locations.create') }}"
                        class="rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-medium text-amber-800"
                    >
                        Cadastrar local
                    </a>

                @endif

            </div>

        </section>

    @else

        <form
            method="POST"
            action="{{ route('schedules.store') }}"
            class="space-y-6"
        >

            @csrf


            {{-- Atendimento --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Atendimento
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Defina o profissional, especialidade e local onde os atendimentos ocorrerão.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

                    {{-- Profissional --}}
                    <div class="md:col-span-2">

                        <label
                            for="professional_user_id"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Profissional *
                        </label>

                        <select
                            id="professional_user_id"
                            name="professional_user_id"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            <option value="">
                                Selecione o profissional
                            </option>

                            @foreach($professionals as $professional)

                                <option
                                    value="{{ $professional->id }}"
                                    @selected(
                                        (string) old('professional_user_id')
                                        === (string) $professional->id
                                    )
                                >
                                    {{ $professional->name }}
                                    - {{ $professional->email }}
                                </option>

                            @endforeach

                        </select>

                        @error('professional_user_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Nome da agenda --}}
                    <div class="md:col-span-2">

                        <label
                            for="name"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Nome da agenda
                        </label>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            maxlength="255"
                            value="{{ old('name') }}"
                            placeholder="Ex.: Ambulatório de Cardiologia"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                        <p class="mt-2 text-xs text-slate-400">
                            O nome pode ser personalizado. Especialidade e local são cadastros padronizados.
                        </p>

                        @error('name')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Especialidade --}}
                    <div>

                        <label
                            for="specialty_id"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Especialidade *
                        </label>

                        <select
                            id="specialty_id"
                            name="specialty_id"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            <option value="">
                                Selecione a especialidade
                            </option>

                            @foreach($specialties as $specialty)

                                <option
                                    value="{{ $specialty->id }}"
                                    @selected(
                                        (string) old('specialty_id')
                                        === (string) $specialty->id
                                    )
                                >
                                    {{ $specialty->name }}

                                    @if($specialty->code)
                                        - {{ $specialty->code }}
                                    @endif
                                </option>

                            @endforeach

                        </select>

                        @error('specialty_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Local --}}
                    <div>

                        <label
                            for="service_location_id"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Local de atendimento *
                        </label>

                        <select
                            id="service_location_id"
                            name="service_location_id"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            <option value="">
                                Selecione o local
                            </option>

                            @foreach($serviceLocations as $location)

                                <option
                                    value="{{ $location->id }}"
                                    @selected(
                                        (string) old('service_location_id')
                                        === (string) $location->id
                                    )
                                >
                                    {{ $location->name }}

                                    @if($location->parent)
                                        - {{ $location->parent->name }}
                                    @endif

                                    @if($location->code)
                                        [{{ $location->code }}]
                                    @endif
                                </option>

                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-slate-400">
                            Somente locais ativos da unidade atual são exibidos.
                        </p>

                        @error('service_location_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- Período e recorrência --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Período e recorrência
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Defina a validade e o dia semanal da grade.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">

                    <div>

                        <label
                            for="starts_on"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Início da validade *
                        </label>

                        <input
                            id="starts_on"
                            name="starts_on"
                            type="date"
                            required
                            value="{{ old('starts_on', now()->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                        @error('starts_on')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <div>

                        <label
                            for="ends_on"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Fim da validade
                        </label>

                        <input
                            id="ends_on"
                            name="ends_on"
                            type="date"
                            value="{{ old('ends_on') }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                        <p class="mt-2 text-xs text-slate-400">
                            Deixe vazio para uma grade sem data final.
                        </p>

                        @error('ends_on')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <div>

                        <label
                            for="weekday"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Dia da semana *
                        </label>

                        <select
                            id="weekday"
                            name="weekday"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            <option value="">
                                Selecione
                            </option>

                            @php
                                $weekdays = [
                                    1 => 'Segunda-feira',
                                    2 => 'Terça-feira',
                                    3 => 'Quarta-feira',
                                    4 => 'Quinta-feira',
                                    5 => 'Sexta-feira',
                                    6 => 'Sábado',
                                    0 => 'Domingo',
                                ];
                            @endphp

                            @foreach($weekdays as $weekdayValue => $weekdayLabel)

                                <option
                                    value="{{ $weekdayValue }}"
                                    @selected(
                                        (string) old('weekday')
                                        === (string) $weekdayValue
                                    )
                                >
                                    {{ $weekdayLabel }}
                                </option>

                            @endforeach

                        </select>

                        @error('weekday')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- Horários --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Horários
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Configure o período de atendimento e a duração de cada vaga.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-4">

                    <div>

                        <label
                            for="starts_at"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Início *
                        </label>

                        <input
                            id="starts_at"
                            name="starts_at"
                            type="time"
                            required
                            value="{{ old('starts_at', '08:00') }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                    </div>


                    <div>

                        <label
                            for="ends_at"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Término *
                        </label>

                        <input
                            id="ends_at"
                            name="ends_at"
                            type="time"
                            required
                            value="{{ old('ends_at', '12:00') }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                    </div>


                    <div>

                        <label
                            for="slot_duration_minutes"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Duração *
                        </label>

                        <select
                            id="slot_duration_minutes"
                            name="slot_duration_minutes"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            @foreach([10, 15, 20, 30, 40, 45, 60, 90, 120] as $duration)

                                <option
                                    value="{{ $duration }}"
                                    @selected(
                                        (int) old(
                                            'slot_duration_minutes',
                                            30
                                        ) === $duration
                                    )
                                >
                                    {{ $duration }} minutos
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div>

                        <label
                            for="slot_capacity"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Capacidade *
                        </label>

                        <input
                            id="slot_capacity"
                            name="slot_capacity"
                            type="number"
                            min="1"
                            max="100"
                            required
                            value="{{ old('slot_capacity', 1) }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                        <p class="mt-2 text-xs text-slate-400">
                            Pacientes permitidos por horário.
                        </p>

                    </div>

                </div>


                {{-- Prévia --}}
                <div class="border-t border-slate-200 bg-blue-50 px-6 py-5">

                    <p class="text-sm font-medium text-blue-900">
                        Prévia dos horários
                    </p>

                    <p
                        id="schedule-preview"
                        class="mt-2 text-sm leading-relaxed text-blue-700"
                    >
                        Configure o período para visualizar os horários.
                    </p>

                </div>

            </section>


            {{-- Rodapé --}}
            <div class="sticky bottom-4 z-20">

                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <p class="text-sm font-medium text-slate-900">
                            Criar grade
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Os horários ficarão disponíveis para usuários autorizados a criar agendamentos.
                        </p>

                    </div>


                    <div class="flex flex-col-reverse gap-3 sm:flex-row">

                        <a
                            href="{{ route('schedules.index') }}"
                            class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Salvar grade
                        </button>

                    </div>

                </div>

            </div>

        </form>

    @endif

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const startInput =
        document.getElementById('starts_at');

    const endInput =
        document.getElementById('ends_at');

    const durationInput =
        document.getElementById('slot_duration_minutes');

    const preview =
        document.getElementById('schedule-preview');

    if (
        !startInput ||
        !endInput ||
        !durationInput ||
        !preview
    ) {
        return;
    }


    function toMinutes(value) {

        if (!value) {
            return null;
        }

        const parts = value.split(':');

        return (
            parseInt(parts[0], 10) * 60
            +
            parseInt(parts[1], 10)
        );
    }


    function toTime(minutes) {

        const hours =
            Math.floor(minutes / 60);

        const mins =
            minutes % 60;

        return String(hours).padStart(2, '0')
            + ':'
            + String(mins).padStart(2, '0');
    }


    function updatePreview() {

        const start =
            toMinutes(startInput.value);

        const end =
            toMinutes(endInput.value);

        const duration =
            parseInt(
                durationInput.value,
                10
            );

        if (
            start === null ||
            end === null ||
            !duration ||
            end <= start
        ) {
            preview.textContent =
                'Informe um período e uma duração válidos.';

            return;
        }

        const slots = [];

        for (
            let current = start;
            current + duration <= end;
            current += duration
        ) {
            slots.push(
                toTime(current)
            );
        }

        if (!slots.length) {
            preview.textContent =
                'O período informado não comporta nenhum horário com essa duração.';

            return;
        }

        preview.textContent =
            slots.length
            + (
                slots.length === 1
                    ? ' horário: '
                    : ' horários: '
            )
            + slots.join(', ');
    }


    startInput.addEventListener(
        'change',
        updatePreview
    );

    endInput.addEventListener(
        'change',
        updatePreview
    );

    durationInput.addEventListener(
        'change',
        updatePreview
    );

    updatePreview();

});
</script>

@endsection