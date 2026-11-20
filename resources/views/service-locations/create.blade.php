@extends('layouts.app')

@section('title', 'Novo Local de Atendimento | Vitallis')
@section('header', 'Novo local de atendimento')
@section('subtitle', 'Cadastro da estrutura física da unidade')

@section('content')

<div class="mx-auto max-w-5xl space-y-6">

    {{-- Cabeçalho --}}
    <div>
        <a
            href="{{ route('service-locations.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ? Voltar para locais de atendimento
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Novo local de atendimento
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Cadastre a estrutura física da unidade de forma organizada e hierárquica.
        </p>
    </div>


    {{-- Erros --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-5">
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


    {{-- Unidade atual --}}
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
            Unidade atual
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
            O local será cadastrado exclusivamente nesta unidade.
        </p>
    </div>


    <form
        method="POST"
        action="{{ route('service-locations.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        @csrf

        {{-- Identificação --}}
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informe o tipo e a identificação do local.
            </p>
        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            {{-- Tipo --}}
            <div>
                <label
                    for="type"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Tipo *
                </label>

                <select
                    id="type"
                    name="type"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">
                        Selecione o tipo
                    </option>

                    @foreach($types as $typeKey => $typeLabel)
                        <option
                            value="{{ $typeKey }}"
                            @selected(old('type') === $typeKey)
                        >
                            {{ $typeLabel }}
                        </option>
                    @endforeach
                </select>

                @error('type')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Nome --}}
            <div>
                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Nome *
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name') }}"
                    placeholder="Ex.: Consultório 201"
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
                    Código
                </label>

                <input
                    id="code"
                    name="code"
                    type="text"
                    maxlength="50"
                    value="{{ old('code') }}"
                    placeholder="Ex.: CONS-201"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm uppercase outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                <p class="mt-2 text-xs text-slate-400">
                    Código opcional e único dentro desta unidade.
                </p>

                @error('code')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- Local superior --}}
            <div>
                <label
                    for="parent_id"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Local superior
                </label>

                <select
                    id="parent_id"
                    name="parent_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">
                        Nenhum - nível raiz
                    </option>

                    @foreach($parents as $parent)
                        <option
                            value="{{ $parent->id }}"
                            @selected(
                                (string) old('parent_id')
                                === (string) $parent->id
                            )
                        >
                            {{ $types[$parent->type] ?? $parent->type }}
                            - {{ $parent->name }}
                            @if($parent->code)
                                [{{ $parent->code }}]
                            @endif
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-xs text-slate-400">
                    Selecione o nível imediatamente superior na estrutura física.
                </p>

                @error('parent_id')
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
                    rows="4"
                    placeholder="Informações adicionais sobre o local..."
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >{{ old('description') }}</textarea>

                @error('description')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>


        {{-- Orientação --}}
        <div class="mx-6 mb-6 rounded-xl border border-blue-100 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-900">
                Organização hierárquica
            </p>

            <p class="mt-2 text-sm text-blue-700">
                Cadastre primeiro os níveis superiores. Depois utilize-os no campo
                "Local superior" ao cadastrar os níveis seguintes.
            </p>

            <div class="mt-4 rounded-lg bg-white/70 p-4 text-sm text-blue-800">
                <p class="font-medium">Exemplo:</p>

                <div class="mt-2 space-y-1">
                    <p>Bloco A</p>
                    <p class="pl-4">? 2º Andar</p>
                    <p class="pl-8">? Ala Cardiologia</p>
                    <p class="pl-12">? Consultório 201</p>
                </div>
            </div>
        </div>


        {{-- Rodapé --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="{{ route('service-locations.index') }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Cadastrar local
            </button>

        </div>

    </form>

</div>

@endsection