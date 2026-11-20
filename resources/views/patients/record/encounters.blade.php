<div class="p-6">

    {{-- =========================================================
         CABEÇALHO
    ========================================================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-lg font-semibold text-slate-900">
                Atendimentos
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Histórico cronológico dos atendimentos clínicos do paciente.
            </p>
        </div>

        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
            {{ $medicalEncounters->count() }}
            {{ $medicalEncounters->count() === 1 ? 'atendimento' : 'atendimentos' }}
        </span>

    </div>


    {{-- =========================================================
         SEM ATENDIMENTOS
    ========================================================== --}}
    @if($medicalEncounters->isEmpty())

        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 font-semibold text-emerald-700">
                A
            </div>

            <h3 class="mt-4 font-medium text-slate-800">
                Nenhum atendimento clínico registrado
            </h3>

            <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-slate-500">
                Os atendimentos aparecerão aqui depois que forem iniciados pela Agenda.
            </p>

        </div>

    @else

        {{-- =====================================================
             ATENDIMENTO ATUAL
        ====================================================== --}}
        @php
            $currentEncounter = $medicalEncounters
                ->firstWhere('status', 'in_progress');

            $historicalEncounters = $medicalEncounters
                ->where('status', '!=', 'in_progress');
        @endphp


        @if($currentEncounter)

            <section class="mt-6">

                <div class="mb-3 flex items-center gap-2">

                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-violet-600"></span>

                    <h3 class="text-sm font-bold uppercase tracking-wide text-violet-700">
                        Atendimento em andamento
                    </h3>

                </div>


                <article class="overflow-hidden rounded-xl border border-violet-200 bg-white shadow-md">

                    {{-- Cabeçalho --}}
                    <div class="border-b border-violet-200 bg-violet-50 px-5 py-4">

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            <div>

                                <div class="flex flex-wrap items-center gap-3">

                                    <h4 class="font-semibold text-violet-950">
                                        {{ $currentEncounter->specialty?->name ?? 'Especialidade não informada' }}
                                    </h4>

                                    <span class="rounded-full bg-violet-700 px-3 py-1 text-xs font-bold text-white">
                                        Em atendimento
                                    </span>

                                </div>


                                <p class="mt-2 text-sm text-violet-700">

                                    {{ $currentEncounter->started_at->format('d/m/Y H:i') }}

                                    @if($currentEncounter->serviceLocation)
                                        <span class="mx-1 text-violet-300">
                                            •
                                        </span>

                                        {{ $currentEncounter->serviceLocation->name }}
                                    @endif

                                </p>


                                @if($currentEncounter->professional)

                                    <p class="mt-1 text-sm text-violet-700">
                                        Profissional:
                                        <span class="font-medium">
                                            {{ $currentEncounter->professional->name }}
                                        </span>
                                    </p>

                                @endif

                            </div>


                            <div class="text-left lg:text-right">

                                <p class="text-xs uppercase tracking-wide text-violet-500">
                                    Episódio clínico
                                </p>

                                <p class="mt-1 font-mono font-semibold text-violet-900">
                                    #{{ $currentEncounter->id }}
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Informações --}}
                    <div class="p-5">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Início real
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $currentEncounter->started_at->format('d/m/Y H:i') }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Unidade
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $currentEncounter->unit?->name ?? 'Não informada' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Local
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ $currentEncounter->serviceLocation?->name ?? 'Não informado' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Agendamento
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    #{{ $currentEncounter->appointment_id }}
                                </p>
                            </div>

                        </div>


                        @if($currentEncounter->reason)

                            <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">

                                <p class="text-xs uppercase tracking-wide text-slate-400">
                                    Motivo
                                </p>

                                <p class="mt-2 text-sm text-slate-700">
                                    {{ $currentEncounter->reason }}
                                </p>

                            </div>

                        @endif


                        {{-- Registros do episódio --}}
                        <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-2">

                            <div class="rounded-lg border border-slate-200 p-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Queixa principal
                                </p>

                                @if($currentEncounter->chief_complaint)

                                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">
                                        {{ $currentEncounter->chief_complaint }}
                                    </p>

                                @else

                                    <p class="mt-2 text-sm text-slate-400">
                                        Ainda não registrada.
                                    </p>

                                @endif

                            </div>


                            <div class="rounded-lg border border-slate-200 p-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Avaliação
                                </p>

                                @if($currentEncounter->assessment)

                                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">
                                        {{ $currentEncounter->assessment }}
                                    </p>

                                @else

                                    <p class="mt-2 text-sm text-slate-400">
                                        Ainda não registrada.
                                    </p>

                                @endif

                            </div>

                        </div>


                        {{-- Estado de edição --}}
                        <div class="mt-5 rounded-lg border border-violet-200 bg-violet-50 p-4">

                            @if(
                                (int) $currentEncounter->professional_user_id
                                ===
                                (int) auth()->id()
                                && $canEditClinicalRecord
                            )

                                <p class="font-semibold text-violet-900">
                                    Atendimento sob sua responsabilidade
                                </p>

                                <p class="mt-1 text-sm text-violet-700">
                                    Os registros clínicos deste episódio podem ser alterados enquanto o atendimento permanecer em andamento.
                                </p>

                            @elseif($canEditClinicalRecord)

                                <p class="font-semibold text-violet-900">
                                    Edição clínica autorizada
                                </p>

                                <p class="mt-1 text-sm text-violet-700">
                                    Seu perfil possui autorização excepcional para atuar neste prontuário.
                                </p>

                            @else

                                <p class="font-semibold text-violet-900">
                                    Atendimento disponível para consulta
                                </p>

                                <p class="mt-1 text-sm text-violet-700">
                                    Este episódio está em andamento, mas não está sob sua responsabilidade.
                                </p>

                            @endif

                        </div>

                    </div>

                </article>

            </section>

        @endif


        {{-- =====================================================
             HISTÓRICO
        ====================================================== --}}
        <section class="mt-8">

            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                Histórico
            </h3>


            @if($historicalEncounters->isEmpty())

                <div class="mt-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6">

                    <p class="text-sm text-slate-500">
                        Nenhum atendimento clínico anterior registrado.
                    </p>

                </div>

            @else

                <div class="relative mt-5 space-y-5">

                    <div
                        class="absolute bottom-0 left-[19px] top-0 hidden w-px bg-slate-200 sm:block"
                        aria-hidden="true"
                    ></div>


                    @foreach($historicalEncounters as $encounter)

                        <article class="relative sm:pl-12">

                            <div
                                class="absolute left-2 top-5 hidden h-6 w-6 rounded-full border-4 border-white bg-emerald-600 shadow-sm sm:block"
                            ></div>


                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                                <div class="flex flex-col gap-4 bg-slate-50 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">

                                    <div>

                                        <div class="flex flex-wrap items-center gap-2">

                                            <p class="font-semibold text-slate-900">
                                                {{ $encounter->specialty?->name ?? 'Especialidade não informada' }}
                                            </p>


                                            @if($encounter->status === 'completed')

                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                    Finalizado
                                                </span>

                                            @elseif($encounter->status === 'cancelled')

                                                <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                    Cancelado
                                                </span>

                                            @else

                                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600">
                                                    {{ $encounter->status }}
                                                </span>

                                            @endif

                                        </div>


                                        <p class="mt-2 text-sm text-slate-600">

                                            {{ $encounter->started_at->format('d/m/Y H:i') }}

                                            @if($encounter->serviceLocation)
                                                • {{ $encounter->serviceLocation->name }}
                                            @endif

                                        </p>


                                        @if($encounter->professional)

                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $encounter->professional->name }}
                                            </p>

                                        @endif

                                    </div>


                                    <div class="text-left lg:text-right">

                                        @if($encounter->completed_at)

                                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                                Finalizado em
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-700">
                                                {{ $encounter->completed_at->format('d/m/Y H:i') }}
                                            </p>

                                        @endif

                                    </div>

                                </div>


                                <div class="p-5">

                                    @if($encounter->reason)

                                        <p class="text-sm text-slate-600">
                                            <span class="font-medium text-slate-700">
                                                Motivo:
                                            </span>

                                            {{ $encounter->reason }}
                                        </p>

                                    @endif


                                    @if($encounter->assessment)

                                        <div class="mt-4">

                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                Avaliação
                                            </p>

                                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">
                                                {{ $encounter->assessment }}
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

            @endif

        </section>

    @endif

</div>