@extends('layouts.app')

@section('title', 'Nova Unidade | Vitallis')
@section('header', 'Nova unidade')
@section('subtitle', 'Cadastro de uma nova unidade de atendimento')

@section('content')

<div class="mx-auto max-w-5xl">

    {{-- Cabeçalho --}}
    <div class="mb-6">

        <a
            href="{{ route('units.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para unidades
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Cadastrar unidade
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Cadastre uma unidade de atendimento e vincule-a à rede responsável.
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
        action="{{ route('units.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >

        @csrf


        {{-- ======================================================
             VÍNCULO COM A REDE
        ======================================================= --}}
        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Rede
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Selecione a rede à qual esta unidade pertence.
            </p>

        </div>


        <div class="p-6">

            <label
                for="network_id"
                class="mb-2 block text-sm font-medium text-slate-700"
            >
                Rede *
            </label>

            <select
                id="network_id"
                name="network_id"
                required
                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
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

            @error('network_id')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            @if($networks->isEmpty())

                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">

                    Não existem redes ativas disponíveis.

                    @if(auth()->user()->hasPermission('network.manage'))

                        <a
                            href="{{ route('networks.create') }}"
                            class="ml-1 font-semibold underline"
                        >
                            Cadastrar uma rede
                        </a>

                    @endif

                </div>

            @endif

        </div>


        {{-- ======================================================
             IDENTIFICAÇÃO
        ======================================================= --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Dados principais da unidade.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            {{-- Nome --}}
            <div>

                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Nome da unidade *
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name') }}"
                    placeholder="Ex.: Clínica Salvador"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('name')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Código --}}
            <div>

                <label
                    for="code"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Código interno
                </label>

                <input
                    id="code"
                    name="code"
                    type="text"
                    maxlength="50"
                    value="{{ old('code') }}"
                    placeholder="Ex.: SSA01"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm uppercase outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                <p class="mt-2 text-xs text-slate-400">
                    O código deve ser único dentro da mesma rede.
                </p>

                @error('code')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Documento --}}
            <div>

                <label
                    for="document"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    CNPJ / Documento
                </label>

                <input
                    id="document"
                    name="document"
                    type="text"
                    maxlength="20"
                    value="{{ old('document') }}"
                    placeholder="00.000.000/0000-00"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('document')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ======================================================
             CONTATO
        ======================================================= --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Contato
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Dados de contato da unidade.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            <div>

                <label
                    for="email"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    E-mail
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    maxlength="255"
                    value="{{ old('email') }}"
                    placeholder="unidade@empresa.com.br"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <div>

                <label
                    for="phone"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Telefone
                </label>

                <input
                    id="phone"
                    name="phone"
                    type="text"
                    maxlength="30"
                    value="{{ old('phone') }}"
                    placeholder="(71) 99999-9999"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('phone')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ======================================================
             ENDEREÇO
        ======================================================= --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Endereço
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Localização da unidade de atendimento.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-12">

            {{-- CEP --}}
            <div class="md:col-span-3">

                <label
                    for="postal_code"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    CEP
                </label>

                <input
                    id="postal_code"
                    name="postal_code"
                    type="text"
                    maxlength="10"
                    value="{{ old('postal_code') }}"
                    placeholder="00000-000"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('postal_code')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Logradouro --}}
            <div class="md:col-span-7">

                <label
                    for="street"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Logradouro
                </label>

                <input
                    id="street"
                    name="street"
                    type="text"
                    maxlength="255"
                    value="{{ old('street') }}"
                    placeholder="Rua, avenida, praça..."
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('street')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Número --}}
            <div class="md:col-span-2">

                <label
                    for="number"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Número
                </label>

                <input
                    id="number"
                    name="number"
                    type="text"
                    maxlength="20"
                    value="{{ old('number') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('number')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Complemento --}}
            <div class="md:col-span-4">

                <label
                    for="complement"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Complemento
                </label>

                <input
                    id="complement"
                    name="complement"
                    type="text"
                    maxlength="255"
                    value="{{ old('complement') }}"
                    placeholder="Sala, bloco, andar..."
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('complement')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Bairro --}}
            <div class="md:col-span-4">

                <label
                    for="district"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Bairro
                </label>

                <input
                    id="district"
                    name="district"
                    type="text"
                    maxlength="255"
                    value="{{ old('district') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('district')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Cidade --}}
            <div class="md:col-span-3">

                <label
                    for="city"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Cidade
                </label>

                <input
                    id="city"
                    name="city"
                    type="text"
                    maxlength="255"
                    value="{{ old('city') }}"
                    placeholder="Salvador"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('city')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- UF --}}
            <div class="md:col-span-1">

                <label
                    for="state"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    UF
                </label>

                <input
                    id="state"
                    name="state"
                    type="text"
                    maxlength="2"
                    value="{{ old('state') }}"
                    placeholder="BA"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm uppercase outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('state')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ======================================================
             RODAPÉ
        ======================================================= --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="{{ route('units.index') }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Cancelar
            </a>

            <button
                type="submit"
                @disabled($networks->isEmpty())
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"
            >
                Cadastrar unidade
            </button>

        </div>

    </form>

</div>

@endsection
