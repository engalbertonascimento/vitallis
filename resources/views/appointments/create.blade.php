@extends('layouts.app')

@section('title', 'Novo Agendamento | Vitallis')
@section('header', 'Novo agendamento')
@section('subtitle', 'Agendamento de paciente na unidade atual')

@section('content')

<div class="mx-auto max-w-5xl space-y-6">

    {{-- =========================================================
         CABEÇALHO
    ========================================================== --}}
    <div>

        <a
            href="{{ route('appointments.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ? Voltar para agenda
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Novo agendamento
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Selecione o paciente e um horário pertencente a uma agenda disponível.
        </p>

    </div>


    {{-- =========================================================
         ERROS
    ========================================================== --}}
    @if($errors->any())

        <div class="rounded-xl border border-red-200 bg-red-50 p-5">

            <p class="font-medium text-red-800">
                Não foi possível criar o agendamento.
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         UNIDADE ATUAL
    ========================================================== --}}
    <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">

        <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
            Unidade do atendimento
        </p>

        <p class="mt-1 font-semibold text-emerald-900">
            {{ $unit->name }}
        </p>

        @if($unit->city || $unit->state)

            <p class="mt-1 text-sm text-emerald-700">

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

        <p class="mt-3 text-xs text-emerald-700">
            Para realizar o agendamento em outra unidade, altere primeiro
            a unidade de trabalho no cabeçalho do Vitallis.
        </p>

    </section>


    {{-- =========================================================
         SEM GRADES
    ========================================================== --}}
    @if($schedules->isEmpty())

        <section class="rounded-xl border border-amber-200 bg-amber-50 p-6">

            <h2 class="font-semibold text-amber-900">
                Nenhuma agenda disponível
            </h2>

            <p class="mt-2 text-sm leading-relaxed text-amber-700">
                Não existem grades de atendimento ativas cadastradas
                para esta unidade.
            </p>


            @if(auth()->user()->hasPermission('schedules.create'))

                <p class="mt-3 text-sm font-medium text-amber-800">
                    Seu perfil possui permissão para criar grades de agenda.
                </p>

            @else

                <p class="mt-3 text-sm text-amber-700">
                    Procure o responsável pela administração das agendas
                    da unidade para disponibilizar horários.
                </p>

            @endif

        </section>

    @else

        {{-- =====================================================
             FORMULÁRIO
        ====================================================== --}}
        <form
            method="POST"
            action="{{ route('appointments.store') }}"
            class="space-y-6"
        >

            @csrf


            {{-- =================================================
                 PACIENTE
            ================================================== --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Paciente
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Selecione o paciente que receberá o atendimento.
                    </p>

                </div>


                <div class="p-6">

                    @if($patients->isEmpty())

                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">

                            <p class="text-sm font-medium text-amber-900">
                                Nenhum paciente disponível nesta rede.
                            </p>

                            @if(auth()->user()->hasPermission('patients.create'))

                                <a
                                    href="{{ route('patients.create') }}"
                                    class="mt-3 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-800"
                                >
                                    Cadastrar paciente ?
                                </a>

                            @endif

                        </div>

                    @else

                        <label
                            for="patient_id"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Paciente *
                        </label>

                        <select
                            id="patient_id"
                            name="patient_id"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            <option value="">
                                Selecione o paciente
                            </option>

                            @foreach($patients as $patient)

                                <option
                                    value="{{ $patient->id }}"
                                    @selected(
                                        (string) old('patient_id')
                                        ===
                                        (string) $patient->id
                                    )
                                >
                                    {{ $patient->name }}

                                    @if($patient->social_name)
                                        ({{ $patient->social_name }})
                                    @endif

                                    @if($patient->cpf)
                                        - CPF {{ $patient->cpf }}
                                    @endif
                                </option>

                            @endforeach

                        </select>

                        @error('patient_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    @endif

                </div>

            </section>


            {{-- =================================================
                 AGENDA / PROFISSIONAL
            ================================================== --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Agenda e profissional
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Escolha uma grade existente da unidade.
                    </p>

                </div>


                <div class="p-6">

                    <label
                        for="schedule_id"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Agenda *
                    </label>

                    <select
                        id="schedule_id"
                        name="schedule_id"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >

                        <option value="">
                            Selecione a agenda
                        </option>

                        @foreach($schedules as $schedule)

                            <option
                                value="{{ $schedule->id }}"
                                data-weekday="{{ $schedule->weekday }}"
                                data-starts-on="{{ $schedule->starts_on?->format('Y-m-d') }}"
                                data-ends-on="{{ $schedule->ends_on?->format('Y-m-d') }}"
                                data-start-time="{{ substr($schedule->starts_at, 0, 5) }}"
                                data-end-time="{{ substr($schedule->ends_at, 0, 5) }}"
                                data-duration="{{ $schedule->slot_duration_minutes }}"
                                @selected(
                                    (string) old('schedule_id')
                                    ===
                                    (string) $schedule->id
                                )
                            >

                                @if($schedule->professional)
                                    {{ $schedule->professional->name }}
                                @else
                                    Profissional não identificado
                                @endif

                                @if($schedule->specialty)
                                    - {{ $schedule->specialty }}
                                @endif

                                @if($schedule->name)
                                    - {{ $schedule->name }}
                                @endif

                                ({{ substr($schedule->starts_at, 0, 5) }}
                                às
                                {{ substr($schedule->ends_at, 0, 5) }})

                            </option>

                        @endforeach

                    </select>

                    @error('schedule_id')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror


                    {{-- Informações da grade --}}
                    <div
                        id="schedule-info"
                        class="mt-4 hidden rounded-lg border border-blue-100 bg-blue-50 p-4"
                    >

                        <p class="text-sm font-medium text-blue-900">
                            Informações da agenda
                        </p>

                        <p
                            id="schedule-info-text"
                            class="mt-1 text-sm text-blue-700"
                        ></p>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 DATA E HORÁRIO
            ================================================== --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Data e horário
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        O horário deve corresponder à grade selecionada.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

                    {{-- Data --}}
                    <div>

                        <label
                            for="date"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Data *
                        </label>

                        <input
                            id="date"
                            name="date"
                            type="date"
                            required
                            min="{{ now()->format('Y-m-d') }}"
                            value="{{ old('date') }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                        @error('date')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Horário --}}
                    <div>

                        <label
                            for="time"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Horário *
                        </label>

                        <select
                            id="time"
                            name="time"
                            required
                            disabled
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                            <option value="">
                                Selecione primeiro a agenda e a data
                            </option>

                        </select>

                        <p
                            id="time-help"
                            class="mt-2 text-xs text-slate-400"
                        >
                            Os horários são definidos pela grade da agenda.
                        </p>

                        @error('time')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- =================================================
                 MOTIVO
            ================================================== --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h2 class="font-semibold text-slate-900">
                        Informações administrativas
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Essas informações pertencem ao agendamento e não ao prontuário clínico.
                    </p>

                </div>


                <div class="space-y-6 p-6">

                    <div>

                        <label
                            for="reason"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Motivo do agendamento
                        </label>

                        <input
                            id="reason"
                            name="reason"
                            type="text"
                            maxlength="255"
                            value="{{ old('reason') }}"
                            placeholder="Ex.: Consulta cardiológica"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >

                        @error('reason')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <div>

                        <label
                            for="administrative_notes"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Observações administrativas
                        </label>

                        <textarea
                            id="administrative_notes"
                            name="administrative_notes"
                            rows="4"
                            maxlength="5000"
                            placeholder="Informações administrativas relacionadas ao agendamento."
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >{{ old('administrative_notes') }}</textarea>

                        <p class="mt-2 text-xs text-slate-400">
                            Não utilize este campo para registro de informações clínicas.
                        </p>

                        @error('administrative_notes')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- =================================================
                 CONFIRMAÇÃO
            ================================================== --}}
            <div class="sticky bottom-4 z-20">

                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <p class="text-sm font-medium text-slate-900">
                            Confirmar agendamento
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            O backend validará novamente disponibilidade, capacidade e contexto da unidade.
                        </p>

                    </div>


                    <div class="flex flex-col-reverse gap-3 sm:flex-row">

                        <a
                            href="{{ route('appointments.index') }}"
                            class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            @disabled($patients->isEmpty())
                            class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                        >
                            Agendar paciente
                        </button>

                    </div>

                </div>

            </div>

        </form>

    @endif

</div>


{{-- =========================================================
     HORÁRIOS DA GRADE
========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const scheduleSelect =
        document.getElementById('schedule_id');

    const dateInput =
        document.getElementById('date');

    const timeSelect =
        document.getElementById('time');

    const scheduleInfo =
        document.getElementById('schedule-info');

    const scheduleInfoText =
        document.getElementById('schedule-info-text');

    const timeHelp =
        document.getElementById('time-help');

    if (
        !scheduleSelect ||
        !dateInput ||
        !timeSelect
    ) {
        return;
    }


    const oldTime = @json(old('time'));


    function selectedSchedule() {

        if (!scheduleSelect.value) {
            return null;
        }

        return scheduleSelect.options[
            scheduleSelect.selectedIndex
        ];
    }


    function parseTimeToMinutes(value) {

        if (!value) {
            return null;
        }

        const parts = value.split(':');

        return (
            (parseInt(parts[0], 10) * 60)
            +
            parseInt(parts[1], 10)
        );
    }


    function minutesToTime(minutes) {

        const hours =
            Math.floor(minutes / 60);

        const mins =
            minutes % 60;

        return String(hours).padStart(2, '0')
            + ':'
            + String(mins).padStart(2, '0');
    }


    function updateScheduleInfo() {

        const option = selectedSchedule();

        if (!option) {

            scheduleInfo?.classList.add('hidden');

            return;
        }

        const start =
            option.dataset.startTime;

        const end =
            option.dataset.endTime;

        const duration =
            option.dataset.duration;

        if (
            scheduleInfo &&
            scheduleInfoText
        ) {

            scheduleInfoText.textContent =
                'Atendimento entre '
                + start
                + ' e '
                + end
                + ', com intervalos de '
                + duration
                + ' minutos.';

            scheduleInfo.classList.remove('hidden');
        }
    }


    function updateTimes() {

        const option =
            selectedSchedule();

        const date =
            dateInput.value;

        timeSelect.innerHTML = '';

        if (!option || !date) {

            timeSelect.disabled = true;

            const placeholder =
                document.createElement('option');

            placeholder.value = '';
            placeholder.textContent =
                'Selecione primeiro a agenda e a data';

            timeSelect.appendChild(
                placeholder
            );

            return;
        }


        /*
         * JavaScript:
         * domingo = 0 ... sábado = 6
         */
        const selectedDate =
            new Date(date + 'T12:00:00');

        const selectedWeekday =
            selectedDate.getDay();

        const scheduleWeekday =
            parseInt(
                option.dataset.weekday,
                10
            );


        if (
            selectedWeekday
            !== scheduleWeekday
        ) {

            timeSelect.disabled = true;

            const invalidDay =
                document.createElement('option');

            invalidDay.value = '';

            invalidDay.textContent =
                'A agenda não atende neste dia';

            timeSelect.appendChild(
                invalidDay
            );

            if (timeHelp) {
                timeHelp.textContent =
                    'Escolha uma data correspondente ao dia da semana configurado na agenda.';
            }

            return;
        }


        const startsOn =
            option.dataset.startsOn;

        const endsOn =
            option.dataset.endsOn;


        if (
            startsOn &&
            date < startsOn
        ) {

            timeSelect.disabled = true;

            const invalidPeriod =
                document.createElement('option');

            invalidPeriod.value = '';

            invalidPeriod.textContent =
                'Data anterior ao início da agenda';

            timeSelect.appendChild(
                invalidPeriod
            );

            return;
        }


        if (
            endsOn &&
            date > endsOn
        ) {

            timeSelect.disabled = true;

            const invalidPeriod =
                document.createElement('option');

            invalidPeriod.value = '';

            invalidPeriod.textContent =
                'Data posterior ao fim da agenda';

            timeSelect.appendChild(
                invalidPeriod
            );

            return;
        }


        const start =
            parseTimeToMinutes(
                option.dataset.startTime
            );

        const end =
            parseTimeToMinutes(
                option.dataset.endTime
            );

        const duration =
            parseInt(
                option.dataset.duration,
                10
            );


        if (
            start === null ||
            end === null ||
            !duration ||
            duration <= 0
        ) {
            timeSelect.disabled = true;
            return;
        }


        const placeholder =
            document.createElement('option');

        placeholder.value = '';

        placeholder.textContent =
            'Selecione o horário';

        timeSelect.appendChild(
            placeholder
        );


        for (
            let current = start;
            current + duration <= end;
            current += duration
        ) {

            const value =
                minutesToTime(current);

            const optionElement =
                document.createElement('option');

            optionElement.value = value;
            optionElement.textContent = value;

            if (
                oldTime &&
                oldTime === value
            ) {
                optionElement.selected = true;
            }

            timeSelect.appendChild(
                optionElement
            );
        }


        timeSelect.disabled = false;

        if (timeHelp) {
            timeHelp.textContent =
                'Os horários acima pertencem à grade selecionada. A disponibilidade será confirmada ao salvar.';
        }
    }


    scheduleSelect.addEventListener(
        'change',
        function () {
            updateScheduleInfo();
            updateTimes();
        }
    );


    dateInput.addEventListener(
        'change',
        updateTimes
    );


    updateScheduleInfo();
    updateTimes();

});
</script>

@endsection