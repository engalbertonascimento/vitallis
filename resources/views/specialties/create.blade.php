@extends('layouts.app')

@section('title', 'Nova Especialidade | Vitallis')
@section('header', 'Nova especialidade')
@section('subtitle', 'Cadastro padronizado de especialidade')

@section('content')

<div class="mx-auto max-w-4xl">

    {{-- Cabeçalho --}}
    <div class="mb-6">

        <a
            href="{{ route('specialties.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para especialidades
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Cadastrar especialidade
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Cadastre uma especialidade padronizada para utilização nas agendas e atendimentos.
        </p>

    </div>


    {{-- Erros --}}
    @if($errors->any())

        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">

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


    {{-- Formulário --}}
    <form
        method="POST"
        action="{{ route('specialties.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >

        @csrf


        {{-- Identificação --}}
        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informações padronizadas da especialidade.
            </p>

        </div>


        <div class="space-y-6 p-6">

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
                    placeholder="Ex.: Cardiologia"
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
                    placeholder="Ex.: CARDIO"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm uppercase outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                <p class="mt-2 text-xs text-slate-400">
                    Código interno opcional para integrações e relatórios.
                </p>

                @error('code')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Descrição --}}
            <div>

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
                    placeholder="Descrição da especialidade..."
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >{{ old('description') }}</textarea>

                @error('description')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- Rodapé --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="{{ route('specialties.index') }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Cadastrar especialidade
            </button>

        </div>

    </form>

</div>

@endsection
