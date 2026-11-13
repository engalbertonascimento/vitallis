@extends('layouts.app')

@section('title', 'Nova Rede | Vitallis')
@section('header', 'Nova rede')
@section('subtitle', 'Cadastro de uma nova organização no Vitallis')

@section('content')

<div class="mx-auto max-w-4xl">

    {{-- Cabeçalho --}}
    <div class="mb-6">

        <a
            href="{{ route('networks.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para redes
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Cadastrar rede
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Informe os dados da organização responsável pelas unidades de atendimento.
        </p>

    </div>


    {{-- Erros gerais --}}
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
        action="{{ route('networks.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >

        @csrf

        {{-- Dados principais --}}
        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informações principais da rede.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            {{-- Nome --}}
            <div>

                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Nome da rede *
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name') }}"
                    placeholder="Ex.: Rede Saúde Vida"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('name')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Razão social --}}
            <div>

                <label
                    for="legal_name"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Razão social
                </label>

                <input
                    id="legal_name"
                    name="legal_name"
                    type="text"
                    maxlength="255"
                    value="{{ old('legal_name') }}"
                    placeholder="Razão social da organização"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('legal_name')
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


        {{-- Contato --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Contato
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Dados administrativos da organização.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            {{-- E-mail --}}
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
                    placeholder="contato@empresa.com.br"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Telefone --}}
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


        {{-- Rodapé --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="{{ route('networks.index') }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Cadastrar rede
            </button>

        </div>

    </form>

</div>

@endsection
