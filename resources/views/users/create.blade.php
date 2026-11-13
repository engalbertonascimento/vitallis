@extends('layouts.app')

@section('title', 'Novo Usuário | Vitallis')
@section('header', 'Novo usuário')
@section('subtitle', 'Cadastro e definição inicial de acesso')

@section('content')

<div class="mx-auto max-w-5xl">

    {{-- Cabeçalho --}}
    <div class="mb-6">

        <a
            href="{{ route('users.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para usuários
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Cadastrar usuário
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Cadastre o usuário e defina seu primeiro vínculo de acesso ao Vitallis.
        </p>

    </div>


    {{-- Erros --}}
    @if($errors->any())

        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">

            <p class="font-medium text-red-800">
                Verifique os dados informados.
            </p>

            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    <form
        method="POST"
        action="{{ route('users.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >

        @csrf


        {{-- =====================================================
             IDENTIFICAÇÃO
        ====================================================== --}}
        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Dados utilizados para identificação e acesso ao sistema.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            {{-- Nome --}}
            <div>

                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Nome completo *
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name') }}"
                    placeholder="Nome do usuário"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('name')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- E-mail --}}
            <div>

                <label
                    for="email"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    E-mail *
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    maxlength="255"
                    value="{{ old('email') }}"
                    placeholder="usuario@empresa.com.br"
                    autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Senha --}}
            <div>

                <label
                    for="password"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Senha *
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Mínimo de 8 caracteres"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('password')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Confirmação --}}
            <div>

                <label
                    for="password_confirmation"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Confirmar senha *
                </label>

                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Repita a senha"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

            </div>

        </div>


        {{-- =====================================================
             ACESSO
        ====================================================== --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Perfil de acesso
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Defina a função e o contexto inicial de atuação do usuário.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            {{-- Função --}}
            <div class="md:col-span-2">

                <label
                    for="role_id"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Função *
                </label>

                <select
                    id="role_id"
                    name="role_id"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                    <option value="">
                        Selecione uma função
                    </option>

                    @foreach($roles as $role)

                        <option
                            value="{{ $role->id }}"
                            data-role-slug="{{ $role->slug }}"
                            @selected((string) old('role_id') === (string) $role->id)
                        >
                            {{ $role->name }}
                        </option>

                    @endforeach

                </select>

                @error('role_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Rede --}}
            <div id="network-field">

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
                            @selected((string) old('network_id') === (string) $network->id)
                        >
                            {{ $network->name }}
                        </option>

                    @endforeach

                </select>

                <p class="mt-2 text-xs text-slate-400">
                    Obrigatória para usuários que não sejam administradores globais.
                </p>

                @error('network_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Unidade --}}
            <div id="unit-field">

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
                        Todas as unidades / não definida
                    </option>

                    @foreach($units as $unit)

                        <option
                            value="{{ $unit->id }}"
                            data-network-id="{{ $unit->network_id }}"
                            @selected((string) old('unit_id') === (string) $unit->id)
                        >
                            {{ $unit->name }}
                        </option>

                    @endforeach

                </select>

                <p class="mt-2 text-xs text-slate-400">
                    Quando vazia, o vínculo poderá valer para toda a rede, conforme a função.
                </p>

                @error('unit_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- Explicação do contexto --}}
        <div class="mx-6 mb-6 rounded-xl border border-blue-100 bg-blue-50 p-4">

            <p class="text-sm font-medium text-blue-900">
                Contexto de acesso
            </p>

            <p class="mt-1 text-sm leading-relaxed text-blue-700">
                A função determina o que o usuário pode fazer. A rede e a unidade determinam onde essas permissões poderão ser utilizadas.
            </p>

        </div>


        {{-- =====================================================
             RODAPÉ
        ====================================================== --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="{{ route('users.index') }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Cadastrar usuário
            </button>

        </div>

    </form>

</div>


{{-- Filtro visual de rede/unidade --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role_id');
    const networkSelect = document.getElementById('network_id');
    const unitSelect = document.getElementById('unit_id');

    const networkField = document.getElementById('network-field');
    const unitField = document.getElementById('unit-field');

    const unitOptions = Array.from(unitSelect.options);

    function selectedRoleSlug() {
        const option = roleSelect.options[roleSelect.selectedIndex];

        return option
            ? option.dataset.roleSlug || ''
            : '';
    }

    function updateRoleContext() {
        const isVitallisAdmin =
            selectedRoleSlug() === 'vitallis-admin';

        networkField.classList.toggle(
            'opacity-50',
            isVitallisAdmin
        );

        unitField.classList.toggle(
            'opacity-50',
            isVitallisAdmin
        );

        networkSelect.disabled = isVitallisAdmin;
        unitSelect.disabled = isVitallisAdmin;

        if (isVitallisAdmin) {
            networkSelect.value = '';
            unitSelect.value = '';
        }

        updateUnitOptions();
    }

    function updateUnitOptions() {
        const networkId = networkSelect.value;
        const currentUnit = unitSelect.value;

        unitOptions.forEach(function (option) {
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

        const selectedOption =
            unitSelect.options[unitSelect.selectedIndex];

        if (
            selectedOption &&
            selectedOption.value &&
            selectedOption.disabled
        ) {
            unitSelect.value = '';
        }

        if (
            currentUnit &&
            Array.from(unitSelect.options).some(
                option =>
                    option.value === currentUnit &&
                    !option.disabled
            )
        ) {
            unitSelect.value = currentUnit;
        }
    }

    roleSelect.addEventListener(
        'change',
        updateRoleContext
    );

    networkSelect.addEventListener(
        'change',
        updateUnitOptions
    );

    updateRoleContext();
});
</script>

@endsection
