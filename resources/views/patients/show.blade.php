@extends('layouts.app')

@section('title', $patient->name . ' | Vitallis')
@section('header', 'Paciente')
@section('subtitle', 'Prontuário e informações clínicas')

@section('content')

@php
    $requestedTab = request('tab', 'summary');

    $availableTabs = [
        'summary' => [
            'label' => 'Resumo',
            'visible' => true,
        ],

        'registration' => [
            'label' => 'Ficha',
            'visible' => true,
        ],

        'encounters' => [
            'label' => 'Atendimentos',
            'visible' => $tabs['encounters'] ?? false,
        ],

        'evolutions' => [
            'label' => 'Evoluções',
            'visible' => $tabs['evolutions'] ?? false,
        ],

        'diagnoses' => [
            'label' => 'Diagnósticos',
            'visible' => $tabs['diagnoses'] ?? false,
        ],

        'prescriptions' => [
            'label' => 'Prescrições',
            'visible' => $tabs['prescriptions'] ?? false,
        ],

        'exams' => [
            'label' => 'Exames',
            'visible' => $tabs['exams'] ?? false,
        ],

        'scales' => [
            'label' => 'Escalas',
            'visible' => $tabs['scales'] ?? false,
        ],

        'documents' => [
            'label' => 'Documentos',
            'visible' => $tabs['documents'] ?? false,
        ],

        'discharge' => [
            'label' => 'Alta',
            'visible' => $tabs['discharge'] ?? false,
        ],
    ];

    $visibleTabs = collect($availableTabs)
        ->filter(
            fn ($tab) => $tab['visible']
        );

    $activeTab = $visibleTabs->has($requestedTab)
        ? $requestedTab
        : 'summary';

    $displayName = $patient->social_name
        ?: $patient->name;

    $activeTabView = match ($activeTab) {
        'registration' =>
            'patients.record.registration',

        'encounters' =>
            'patients.record.encounters',

        'evolutions' =>
            'patients.record.evolutions',

        'diagnoses' =>
            'patients.record.diagnoses',

        'prescriptions' =>
            'patients.record.prescriptions',

        'exams' =>
            'patients.record.exams',

        'scales' =>
            'patients.record.scales',

        'documents' =>
            'patients.record.documents',

        'discharge' =>
            'patients.record.discharge',

        default =>
            'patients.record.summary',
    };
@endphp


<div class="space-y-6">

    {{-- =========================================================
         CABEÇALHO FIXO DO PACIENTE
    ========================================================== --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-6 p-6 xl:flex-row xl:items-center xl:justify-between">

            <div class="min-w-0">

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ $displayName }}
                    </h1>


                    @if(! $patient->active)

                        <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                            Cadastro inativo
                        </span>

                    @endif


                    @if($activeAppointment)

                        <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-800">
                            Em atendimento
                        </span>

                    @endif

                </div>


                @if(
                    $patient->social_name
                    && $patient->social_name !== $patient->name
                )

                    <p class="mt-1 text-sm text-slate-500">
                        Nome civil: {{ $patient->name }}
                    </p>

                @endif


                <div class="mt-5 flex flex-wrap gap-x-8 gap-y-4 text-sm">

                    {{-- Nascimento --}}
                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Nascimento
                        </p>

                        <p class="mt-1 font-medium text-slate-700">
                            {{ $patient->birth_date?->format('d/m/Y') ?? 'Não informado' }}
                        </p>

                    </div>


                    {{-- CPF --}}
                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            CPF
                        </p>

                        <p class="mt-1 font-medium text-slate-700">
                            {{ $patient->cpf ?: 'Não informado' }}
                        </p>

                    </div>


                    {{-- CNS --}}
                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            CNS
                        </p>

                        <p class="mt-1 font-medium text-slate-700">
                            {{ $patient->cns ?: 'Não informado' }}
                        </p>

                    </div>


                    {{-- Sexo --}}
                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Sexo
                        </p>

                        <p class="mt-1 font-medium text-slate-700">
                            {{ $patient->sex ?: 'Não informado' }}
                        </p>

                    </div>


                    {{-- Telefone --}}
                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Telefone
                        </p>

                        <p class="mt-1 font-medium text-slate-700">
                            {{
                                $patient->mobile_phone
                                ?: (
                                    $patient->phone
                                    ?: 'Não informado'
                                )
                            }}
                        </p>

                    </div>


                    {{-- E-mail --}}
                    <div>

                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            E-mail
                        </p>

                        <p class="mt-1 font-medium text-slate-700">
                            {{ $patient->email ?: 'Não informado' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Modo do prontuário --}}
            <div class="shrink-0">

                @if($canEditClinicalRecord)

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                            Prontuário
                        </p>

                        <p class="mt-1 font-semibold text-emerald-900">
                            Edição clínica permitida
                        </p>

                        @if($isResponsibleProfessional)

                            <p class="mt-1 max-w-xs text-xs text-emerald-700">
                                Você é o profissional responsável pelo atendimento em andamento.
                            </p>

                        @elseif($canSupervise)

                            <p class="mt-1 max-w-xs text-xs text-emerald-700">
                                Acesso autorizado por supervisão clínica.
                            </p>

                        @elseif($canWriteOutsideEncounter)

                            <p class="mt-1 max-w-xs text-xs text-emerald-700">
                                Alteração fora do período de atendimento autorizada.
                            </p>

                        @endif

                    </div>

                @else

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Prontuário
                        </p>

                        <p class="mt-1 font-semibold text-slate-700">
                            Modo consulta
                        </p>

                        <p class="mt-1 max-w-xs text-xs text-slate-500">
                            As informações clínicas estão disponíveis somente para visualização.
                        </p>

                    </div>

                @endif

            </div>

        </div>


        {{-- =====================================================
             ATENDIMENTO EM ANDAMENTO
        ====================================================== --}}
        @if($activeAppointment)

            <div class="border-t border-violet-200 bg-violet-50 px-6 py-4">

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wide text-violet-600">
                            Atendimento em andamento
                        </p>

                        <p class="mt-1 font-semibold text-violet-950">

                            {{
                                $activeAppointment
                                    ->schedule
                                    ?->specialtyRelation
                                    ?->name
                                ?? 'Especialidade não informada'
                            }}

                            <span class="mx-1 text-violet-300">
                                •
                            </span>

                            {{ $activeAppointment->starts_at->format('d/m/Y H:i') }}


                            @if(
                                $activeAppointment
                                    ->schedule
                                    ?->serviceLocation
                            )

                                <span class="mx-1 text-violet-300">
                                    •
                                </span>

                                {{
                                    $activeAppointment
                                        ->schedule
                                        ->serviceLocation
                                        ->name
                                }}

                            @endif

                        </p>


                        @if($activeAppointment->professional)

                            <p class="mt-1 text-sm text-violet-700">
                                {{ $activeAppointment->professional->name }}
                            </p>

                        @endif

                    </div>


                    @if($isResponsibleProfessional)

                        <span class="w-fit rounded-full bg-violet-700 px-4 py-2 text-xs font-bold text-white">
                            Atendimento atual
                        </span>

                    @elseif($canSupervise)

                        <span class="w-fit rounded-full bg-white px-4 py-2 text-xs font-semibold text-violet-700">
                            Supervisão autorizada
                        </span>

                    @endif

                </div>

            </div>

        @endif

    </section>


    {{-- =========================================================
         FICHÁRIO
    ========================================================== --}}
    <section>

        {{-- Orelhas --}}
        <div class="flex gap-1 overflow-x-auto px-2 pt-2">

            @foreach($visibleTabs as $tabKey => $tab)

                <a
                    href="{{ route('patients.show', [
                        'patient' => $patient->id,
                        'tab' => $tabKey,
                    ]) }}"
                    class="
                        relative whitespace-nowrap rounded-t-xl border px-4 py-3 text-sm font-semibold transition

                        {{
                            $activeTab === $tabKey
                                ? 'z-10 -mb-px border-slate-300 border-b-white bg-white text-emerald-800 shadow-sm'
                                : 'border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800'
                        }}
                    "
                >
                    {{ $tab['label'] }}

                    @if(
                        $tabKey === 'encounters'
                        && $activeAppointment
                    )

                        <span class="ml-1 inline-block h-2 w-2 rounded-full bg-violet-600"></span>

                    @endif

                </a>

            @endforeach

        </div>


        {{-- Folha selecionada --}}
        <div class="min-h-[520px] rounded-b-2xl rounded-tr-2xl border border-slate-300 bg-white shadow-sm">

            @include($activeTabView, [
                'tabLabel' =>
                    $availableTabs[$activeTab]['label'],

                'patient' =>
                    $patient,

                'activeAppointment' =>
                    $activeAppointment,

                'isResponsibleProfessional' =>
                    $isResponsibleProfessional,

                'canEditClinicalRecord' =>
                    $canEditClinicalRecord,

                'recordMode' =>
                    $recordMode,
            ])

        </div>

    </section>

</div>

@endsection