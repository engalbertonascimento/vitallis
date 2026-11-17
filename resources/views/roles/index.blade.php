@extends('layouts.app')

@section('title', 'Funções e Permissões | Vitallis')
@section('header', 'Funções e Permissões')
@section('subtitle', 'Configure as permissões de cada função de acordo com a unidade')

@section('content')

<div class="space-y-6">

    {{-- =========================================================
         CABEÇALHO
    ========================================================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h1 class="text-2xl font-semibold text-slate-900">
                Funções e Permissões
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Defina como cada função poderá operar em cada unidade.
            </p>

        </div>


        {{-- Nova função --}}
        @if(auth()->user()->hasPermission('roles.manage'))

            <a
                href="{{ route('roles.create', [
                    'network_id' => $networkId,
                    'unit_id' => $unitId,
                ]) }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Nova função
            </a>

        @endif

    </div>


    {{-- =========================================================
         EXPLICAÇÃO
    ========================================================== --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">

        <h2 class="font-semibold text-blue-900">
            Permissões contextuais
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-blue-700">
            As permissões padrão pertencem à função. Cada unidade pode
            sobrescrever esse comportamento utilizando as opções
            Herdar, Permitir ou Negar.
        </p>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">

            <div class="rounded-lg bg-white/70 p-3">

                <p class="text-sm font-medium text-slate-800">
                    Herdar
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Utiliza a configuração padrão da função.
                </p>

            </div>


            <div class="rounded-lg bg-white/70 p-3">

                <p class="text-sm font-medium text-emerald-700">
                    Permitir
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Libera explicitamente a ação nesta unidade.
                </p>

            </div>


            <div class="rounded-lg bg-white/70 p-3">

                <p class="text-sm font-medium text-red-700">
                    Negar
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Bloqueia explicitamente a ação nesta unidade.
                </p>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CONTEXTO
    ========================================================== --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Contexto da configuração
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Selecione a rede e a unidade que deseja configurar.
            </p>

        </div>


        <form
            method="GET"
            action="{{ route('roles.index') }}"
            class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2"
        >

            {{-- Rede --}}
            <div>

                <label
                    for="network_id"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Rede
                </label>

                <select
                    id="network_id"
                    name="network_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                    <option value="">
                        Selecione uma rede
                    </option>

                    @foreach($networks as $network)

                        <option
                            value="{{ $network->id }}"
                            @selected(
                                (string) $networkId
                                ===
                                (string) $network->id
                            )
                        >
                            {{ $network->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Unidade --}}
            <div>

                <label
                    for="unit_id"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Unidade
                </label>

                <select
                    id="unit_id"
                    name="unit_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                    <option value="">
                        Selecione uma unidade
                    </option>

                    @foreach($units as $unit)

                        <option
                            value="{{ $unit->id }}"
                            data-network-id="{{ $unit->network_id }}"
                            @selected(
                                (string) $unitId
                                ===
                                (string) $unit->id
                            )
                        >

                            {{ $unit->name }}

                            @if($unit->city)
                                - {{ $unit->city }}
                            @endif

                            @if($unit->state)
                                / {{ $unit->state }}
                            @endif

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="flex justify-end md:col-span-2">

                <button
                    type="submit"
                    class="rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                >
                    Aplicar contexto
                </button>

            </div>

        </form>

    </section>


    {{-- =========================================================
         FUNÇÕES
    ========================================================== --}}
    @if(!$unitId)

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6">

            <h2 class="font-semibold text-amber-900">
                Selecione uma unidade
            </h2>

            <p class="mt-2 text-sm text-amber-700">
                Para configurar permissões específicas, primeiro selecione
                a rede e a unidade desejada.
            </p>

            @if(auth()->user()->hasPermission('roles.manage'))

                <p class="mt-3 text-sm text-amber-700">
                    Você também pode utilizar o botão
                    <strong>Nova função</strong> para criar uma função
                    global ou vinculada a uma rede.
                </p>

            @endif

        </div>

    @else

        @php
            $selectedUnit = $units->firstWhere(
                'id',
                (int) $unitId
            );
        @endphp


        {{-- =====================================================
             UNIDADE SELECIONADA
        ====================================================== --}}
        @if($selectedUnit)

            <div class="flex flex-col gap-4 rounded-xl border border-emerald-200 bg-emerald-50 p-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
                        Unidade selecionada
                    </p>

                    <p class="mt-1 font-semibold text-emerald-900">
                        {{ $selectedUnit->name }}
                    </p>

                    @if($selectedUnit->network)

                        <p class="mt-1 text-sm text-emerald-700">
                            {{ $selectedUnit->network->name }}
                        </p>

                    @endif

                </div>


                <div class="flex flex-wrap gap-2">

                    <span class="inline-flex w-fit rounded-full bg-white px-3 py-1 text-xs font-medium text-emerald-700 shadow-sm">
                        Configuração por unidade
                    </span>

                    @if(auth()->user()->hasPermission('roles.manage'))

                        <a
                            href="{{ route('roles.create', [
                                'network_id' => $selectedUnit->network_id,
                                'unit_id' => $selectedUnit->id,
                            ]) }}"
                            class="inline-flex w-fit rounded-full bg-emerald-700 px-3 py-1 text-xs font-medium text-white transition hover:bg-emerald-800"
                        >
                            + Função nesta unidade
                        </a>

                    @endif

                </div>

            </div>

        @endif


        {{-- =====================================================
             CARDS DAS FUNÇÕES
        ====================================================== --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            @forelse($roles as $role)

                <article
                    class="flex flex-col rounded-xl border border-slate-200 bg-white shadow-sm"
                >

                    {{-- Conteúdo --}}
                    <div class="flex-1 p-6">

                        <div class="flex items-start justify-between gap-4">

                            <div class="min-w-0">

                                <h2 class="font-semibold text-slate-900">
                                    {{ $role->name }}
                                </h2>

                                @if($role->description)

                                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                        {{ $role->description }}
                                    </p>

                                @endif

                            </div>


                            {{-- Escopo --}}
                            <div class="flex shrink-0 flex-col items-end gap-2">

                                @if($role->system)

                                    <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700">
                                        Sistema
                                    </span>

                                @endif


                                @if($role->scope_type === 'global')

                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                        Global
                                    </span>

                                @elseif($role->scope_type === 'network')

                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                        Rede
                                    </span>

                                @elseif($role->scope_type === 'unit')

                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Unidade
                                    </span>

                                @endif

                            </div>

                        </div>


                        {{-- Contexto da função --}}
                        @if($role->scope_type === 'network' && $role->network)

                            <div class="mt-4 rounded-lg bg-amber-50 px-3 py-2">

                                <p class="text-xs font-medium uppercase tracking-wide text-amber-600">
                                    Rede
                                </p>

                                <p class="mt-1 text-sm font-medium text-amber-900">
                                    {{ $role->network->name }}
                                </p>

                            </div>

                        @elseif($role->scope_type === 'unit' && $role->unit)

                            <div class="mt-4 rounded-lg bg-emerald-50 px-3 py-2">

                                <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
                                    Unidade exclusiva
                                </p>

                                <p class="mt-1 text-sm font-medium text-emerald-900">
                                    {{ $role->unit->name }}
                                </p>

                                @if($role->network)

                                    <p class="mt-0.5 text-xs text-emerald-700">
                                        {{ $role->network->name }}
                                    </p>

                                @endif

                            </div>

                        @endif


                        {{-- Quantidade de permissões --}}
                        <div class="mt-5 flex items-center gap-2">

                            <span class="text-2xl font-semibold text-slate-900">
                                {{ $role->permissions_count }}
                            </span>

                            <span class="text-sm text-slate-500">
                                permissões padrão
                            </span>

                        </div>

                    </div>


                    {{-- Ações --}}
                    <div class="border-t border-slate-200 bg-slate-50 p-4">

                        <a
                            href="{{ route('roles.edit', [
                                'role' => $role->id,
                                'unit_id' => $unitId,
                            ]) }}"
                            class="flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                        >
                            Configurar permissões
                        </a>

                    </div>

                </article>

            @empty

                <div class="rounded-xl border border-slate-200 bg-white p-10 text-center md:col-span-2 xl:col-span-3">

                    <p class="font-medium text-slate-700">
                        Nenhuma função disponível.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Não existem funções para o contexto selecionado.
                    </p>


                    @if(auth()->user()->hasPermission('roles.manage'))

                        <a
                            href="{{ route('roles.create', [
                                'network_id' => $networkId,
                                'unit_id' => $unitId,
                            ]) }}"
                            class="mt-5 inline-flex rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Criar primeira função
                        </a>

                    @endif

                </div>

            @endforelse

        </div>

    @endif

</div>


{{-- =========================================================
     FILTRO DE UNIDADES POR REDE
========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const networkSelect =
        document.getElementById('network_id');

    const unitSelect =
        document.getElementById('unit_id');

    if (!networkSelect || !unitSelect) {
        return;
    }

    const options =
        Array.from(unitSelect.options);


    function filterUnits() {

        const networkId =
            networkSelect.value;

        options.forEach(function (option) {

            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const belongs =
                !networkId ||
                option.dataset.networkId === networkId;

            option.hidden = !belongs;
            option.disabled = !belongs;

        });


        const selected =
            unitSelect.options[
                unitSelect.selectedIndex
            ];

        if (
            selected &&
            selected.value &&
            selected.disabled
        ) {
            unitSelect.value = '';
        }
    }


    networkSelect.addEventListener(
        'change',
        function () {

            /*
             * Ao mudar de rede, removemos uma unidade
             * que pertencia ao contexto anterior.
             */
            unitSelect.value = '';

            filterUnits();
        }
    );


    filterUnits();

});
</script>

@endsection