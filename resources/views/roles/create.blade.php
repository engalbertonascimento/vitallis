@extends('layouts.app')

@section('title', 'Nova Função | Vitallis')
@section('header', 'Nova função')
@section('subtitle', 'Cadastro de função e definição inicial de permissões')

@section('content')

@php
    $groupLabels = [
        'dashboard' => 'Dashboard',
        'patients' => 'Pacientes',
        'appointments' => 'Agendamentos',
        'schedules' => 'Grades de Agenda',
        'medical_records' => 'Prontuário',
        'prescriptions' => 'Prescrições',
        'exams' => 'Exames',
        'users' => 'Usuários',
        'roles' => 'Funções',
        'permissions' => 'Permissões',
        'unit_permissions' => 'Permissões por Unidade',
        'user_permissions' => 'Permissões Especiais de Usuários',
        'reports' => 'Relatórios',
        'network' => 'Redes',
        'unit' => 'Unidades',
        'audit' => 'Auditoria',
    ];
@endphp


<div class="mx-auto max-w-6xl space-y-6">

    {{-- =========================================================
         CABEÇALHO
    ========================================================== --}}
    <div>

        <a
            href="{{ route('roles.index', [
                'network_id' => request('network_id'),
                'unit_id' => request('unit_id'),
            ]) }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para funções e permissões
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Criar função
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Crie uma função global, vinculada a uma rede ou exclusiva de uma unidade.
        </p>

    </div>


    {{-- =========================================================
         ERROS
    ========================================================== --}}
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


    {{-- =========================================================
         EXPLICAÇÃO DOS ESCOPOS
    ========================================================== --}}
    <section class="rounded-xl border border-blue-100 bg-blue-50 p-5">

        <h2 class="font-semibold text-blue-900">
            Escopo da função
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

            <div class="rounded-lg bg-white/70 p-4">

                <p class="font-medium text-blue-900">
                    Global
                </p>

                <p class="mt-1 text-sm leading-relaxed text-blue-700">
                    Disponível para todas as redes e unidades do Vitallis.
                </p>

            </div>


            <div class="rounded-lg bg-white/70 p-4">

                <p class="font-medium text-blue-900">
                    Rede
                </p>

                <p class="mt-1 text-sm leading-relaxed text-blue-700">
                    Disponível somente dentro da rede selecionada.
                </p>

            </div>


            <div class="rounded-lg bg-white/70 p-4">

                <p class="font-medium text-blue-900">
                    Unidade
                </p>

                <p class="mt-1 text-sm leading-relaxed text-blue-700">
                    Disponível somente na unidade selecionada.
                </p>

            </div>

        </div>

    </section>


    <form
        method="POST"
        action="{{ route('roles.store') }}"
        class="space-y-6"
    >

        @csrf


        {{-- =====================================================
             IDENTIFICAÇÃO
        ====================================================== --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="font-semibold text-slate-900">
                    Identificação
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Informações principais da função.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

                {{-- Nome --}}
                <div>

                    <label
                        for="name"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Nome da função *
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        maxlength="255"
                        value="{{ old('name') }}"
                        placeholder="Ex.: Contas Médicas"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Slug --}}
                <div>

                    <label
                        for="slug"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Identificador
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        type="text"
                        maxlength="255"
                        value="{{ old('slug') }}"
                        placeholder="Gerado automaticamente se estiver vazio"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm lowercase outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >

                    <p class="mt-2 text-xs text-slate-400">
                        Exemplo: contas-medicas-hospital-sao-rafael
                    </p>

                    @error('slug')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Descrição --}}
                <div class="md:col-span-2">

                    <label
                        for="description"
                        class="mb-2 block text-sm font-medium text-slate-700"
                    >
                        Descrição
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        maxlength="255"
                        placeholder="Descreva as responsabilidades desta função."
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

        </section>


        {{-- =====================================================
             ESCOPO
        ====================================================== --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="font-semibold text-slate-900">
                    Escopo
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Defina onde a função estará disponível.
                </p>

            </div>


            <div class="space-y-6 p-6">

                {{-- Tipo de escopo --}}
                <div>

                    <p class="mb-3 text-sm font-medium text-slate-700">
                        Tipo de escopo *
                    </p>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">

                        {{-- Global --}}
                        <label class="cursor-pointer rounded-xl border border-slate-200 p-4 transition hover:border-emerald-400 hover:bg-emerald-50">

                            <div class="flex items-start gap-3">

                                <input
                                    type="radio"
                                    name="scope_type"
                                    value="global"
                                    @checked(old('scope_type', 'unit') === 'global')
                                    class="mt-1 h-4 w-4 border-slate-300 text-emerald-700 focus:ring-emerald-600"
                                >

                                <div>

                                    <span class="block text-sm font-semibold text-slate-900">
                                        Global
                                    </span>

                                    <span class="mt-1 block text-xs leading-relaxed text-slate-500">
                                        Todas as redes e unidades.
                                    </span>

                                </div>

                            </div>

                        </label>


                        {{-- Rede --}}
                        <label class="cursor-pointer rounded-xl border border-slate-200 p-4 transition hover:border-emerald-400 hover:bg-emerald-50">

                            <div class="flex items-start gap-3">

                                <input
                                    type="radio"
                                    name="scope_type"
                                    value="network"
                                    @checked(old('scope_type') === 'network')
                                    class="mt-1 h-4 w-4 border-slate-300 text-emerald-700 focus:ring-emerald-600"
                                >

                                <div>

                                    <span class="block text-sm font-semibold text-slate-900">
                                        Rede
                                    </span>

                                    <span class="mt-1 block text-xs leading-relaxed text-slate-500">
                                        Somente na rede selecionada.
                                    </span>

                                </div>

                            </div>

                        </label>


                        {{-- Unidade --}}
                        <label class="cursor-pointer rounded-xl border border-slate-200 p-4 transition hover:border-emerald-400 hover:bg-emerald-50">

                            <div class="flex items-start gap-3">

                                <input
                                    type="radio"
                                    name="scope_type"
                                    value="unit"
                                    @checked(old('scope_type', 'unit') === 'unit')
                                    class="mt-1 h-4 w-4 border-slate-300 text-emerald-700 focus:ring-emerald-600"
                                >

                                <div>

                                    <span class="block text-sm font-semibold text-slate-900">
                                        Unidade
                                    </span>

                                    <span class="mt-1 block text-xs leading-relaxed text-slate-500">
                                        Somente na unidade selecionada.
                                    </span>

                                </div>

                            </div>

                        </label>

                    </div>

                    @error('scope_type')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- Rede --}}
                    <div id="network-scope-field">

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
                                        (string) old(
                                            'network_id',
                                            request('network_id')
                                        ) === (string) $network->id
                                    )
                                >
                                    {{ $network->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('network_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Unidade --}}
                    <div id="unit-scope-field">

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
                                        (string) old(
                                            'unit_id',
                                            request('unit_id')
                                        ) === (string) $unit->id
                                    )
                                >

                                    {{ $unit->name }}

                                    @if($unit->city)
                                        - {{ $unit->city }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @error('unit_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
             PERMISSÕES PADRÃO
        ====================================================== --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="font-semibold text-slate-900">
                    Permissões padrão
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Selecione as permissões iniciais desta função.
                </p>

            </div>


            <div class="space-y-8 p-6">

                @foreach($permissionGroups as $group => $groupPermissions)

                    <div>

                        <h3 class="mb-3 text-sm font-semibold text-slate-900">
                            {{ $groupLabels[$group] ?? ucfirst(str_replace('_', ' ', $group)) }}
                        </h3>


                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                            @foreach($groupPermissions as $permission)

                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50">

                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        @checked(
                                            in_array(
                                                $permission->id,
                                                old('permissions', []),
                                                false
                                            )
                                        )
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600"
                                    >

                                    <span>

                                        <span class="block text-sm font-medium text-slate-800">
                                            {{ $permission->name }}
                                        </span>

                                        <span class="mt-1 block font-mono text-xs text-slate-400">
                                            {{ $permission->slug }}
                                        </span>

                                    </span>

                                </label>

                            @endforeach

                        </div>

                    </div>

                @endforeach

            </div>

        </section>


        {{-- =====================================================
             RODAPÉ
        ====================================================== --}}
        <div class="sticky bottom-4 z-20">

            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-sm font-medium text-slate-900">
                        Nova função
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        A função poderá ser ajustada posteriormente na matriz de permissões.
                    </p>

                </div>


                <div class="flex flex-col-reverse gap-3 sm:flex-row">

                    <a
                        href="{{ route('roles.index', [
                            'network_id' => request('network_id'),
                            'unit_id' => request('unit_id'),
                        ]) }}"
                        class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Criar função
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const scopeInputs = document.querySelectorAll(
        'input[name="scope_type"]'
    );

    const networkField = document.getElementById(
        'network-scope-field'
    );

    const unitField = document.getElementById(
        'unit-scope-field'
    );

    const networkSelect = document.getElementById(
        'network_id'
    );

    const unitSelect = document.getElementById(
        'unit_id'
    );

    if (
        !networkField ||
        !unitField ||
        !networkSelect ||
        !unitSelect
    ) {
        return;
    }

    const unitOptions = Array.from(
        unitSelect.options
    );


    function selectedScope() {

        const selected = document.querySelector(
            'input[name="scope_type"]:checked'
        );

        return selected
            ? selected.value
            : 'unit';
    }


    function updateScopeFields() {

        const scope = selectedScope();

        const usesNetwork =
            scope === 'network' ||
            scope === 'unit';

        const usesUnit =
            scope === 'unit';


        networkField.classList.toggle(
            'hidden',
            !usesNetwork
        );

        unitField.classList.toggle(
            'hidden',
            !usesUnit
        );

        networkSelect.disabled = !usesNetwork;
        unitSelect.disabled = !usesUnit;


        if (!usesNetwork) {
            networkSelect.value = '';
        }

        if (!usesUnit) {
            unitSelect.value = '';
        }

        filterUnits();
    }


    function filterUnits() {

        const networkId = networkSelect.value;
        const currentUnitId = unitSelect.value;

        unitOptions.forEach(function (option) {

            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const belongs =
                networkId !== '' &&
                option.dataset.networkId === networkId;

            option.hidden = !belongs;
            option.disabled = !belongs;

        });


        const currentOption = unitOptions.find(
            option =>
                option.value === currentUnitId
        );

        if (
            currentOption &&
            currentOption.value &&
            currentOption.disabled
        ) {
            unitSelect.value = '';
        }
    }


    scopeInputs.forEach(function (input) {

        input.addEventListener(
            'change',
            updateScopeFields
        );

    });


    networkSelect.addEventListener(
        'change',
        function () {
            unitSelect.value = '';
            filterUnits();
        }
    );


    updateScopeFields();

});
</script>

@endsection
