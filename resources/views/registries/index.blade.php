@extends('layouts.app')

@section('title', 'Demais Cadastros | Vitallis')
@section('header', 'Demais Cadastros')
@section('subtitle', 'Cadastros auxiliares e parametrizações do sistema')

@section('content')

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div>

        <h1 class="text-2xl font-semibold text-slate-900">
            Demais Cadastros
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Gerencie cadastros padronizados utilizados pelos módulos assistenciais e administrativos do Vitallis.
        </p>

    </div>


    {{-- Informação --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">

        <h2 class="font-semibold text-blue-900">
            Padronização de dados
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-blue-700">
            Estes cadastros evitam informações livres e divergentes,
            garantindo consistência para agendas, atendimentos,
            filtros, integrações e relatórios.
        </p>

    </div>


    {{-- Cards --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">


        {{-- Especialidades --}}
        @if(auth()->user()->hasPermission('specialties.view'))

            <article class="flex flex-col rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="flex-1 p-6">

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <h2 class="text-lg font-semibold text-slate-900">
                                Especialidades
                            </h2>

                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                Cadastro padronizado das especialidades utilizadas
                                nas grades de agenda e atendimentos.
                            </p>

                        </div>

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 font-semibold text-emerald-700">
                            E
                        </div>

                    </div>


                    <div class="mt-5 rounded-lg bg-slate-50 p-4">

                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Exemplos
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            Cardiologia, Clínica Médica, Ortopedia,
                            Pediatria, Neurologia.
                        </p>

                    </div>

                </div>


                <div class="border-t border-slate-200 bg-slate-50 p-4">

                    <a
                        href="{{ route('specialties.index') }}"
                        class="flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        Gerenciar especialidades
                    </a>

                </div>

            </article>

        @endif


        {{-- Locais de Atendimento --}}
        @if(auth()->user()->hasPermission('service_locations.view'))

            <article class="flex flex-col rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="flex-1 p-6">

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <h2 class="text-lg font-semibold text-slate-900">
                                Locais de Atendimento
                            </h2>

                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                Estruture os locais físicos da unidade de forma
                                hierárquica e padronizada.
                            </p>

                        </div>

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 font-semibold text-blue-700">
                            L
                        </div>

                    </div>


                    <div class="mt-5 rounded-lg bg-slate-50 p-4">

                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Estrutura
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            Edifícios, blocos, andares, alas, setores,
                            salas, consultórios e leitos.
                        </p>

                    </div>

                </div>


                <div class="border-t border-slate-200 bg-slate-50 p-4">

                    <a
                        href="{{ route('service-locations.index') }}"
                        class="flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        Gerenciar locais
                    </a>

                </div>

            </article>

        @endif


        {{-- Espaço preparado para futuros cadastros --}}
        <article class="flex flex-col rounded-xl border border-dashed border-slate-300 bg-slate-50">

            <div class="flex-1 p-6">

                <h2 class="font-semibold text-slate-700">
                    Novos cadastros
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Outros cadastros parametrizáveis do Vitallis serão organizados nesta área.
                </p>


                <div class="mt-5 flex flex-wrap gap-2">

                    <span class="rounded-full bg-white px-3 py-1 text-xs text-slate-500">
                        Procedimentos
                    </span>

                    <span class="rounded-full bg-white px-3 py-1 text-xs text-slate-500">
                        Convênios
                    </span>

                    <span class="rounded-full bg-white px-3 py-1 text-xs text-slate-500">
                        Tipos de atendimento
                    </span>

                </div>

            </div>

        </article>

    </div>

</div>

@endsection