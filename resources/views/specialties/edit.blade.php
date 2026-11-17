@extends('layouts.app')

@section('title', 'Editar Especialidade | Vitallis')
@section('header', 'Editar especialidade')
@section('subtitle', 'Atualização do cadastro padronizado')

@section('content')

<div class="mx-auto max-w-4xl">

    <div class="mb-6">
        <a
            href="{{ route('specialties.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para especialidades
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            {{ $specialty->name }}
        </h1>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('specialties.update', $specialty) }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        @csrf
        @method('PUT')

        <div class="space-y-6 p-6">

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">
                    Nome *
                </label>

                <input
                    id="name"
                    name="name"
                    required
                    maxlength="255"
                    value="{{ old('name', $specialty->name) }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>

            <div>
                <label for="code" class="mb-2 block text-sm font-medium text-slate-700">
                    Código
                </label>

                <input
                    id="code"
                    name="code"
                    maxlength="50"
                    value="{{ old('code', $specialty->code) }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm uppercase outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium text-slate-700">
                    Descrição
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >{{ old('description', $specialty->description) }}</textarea>
            </div>

        </div>

        <div class="flex justify-between border-t border-slate-200 bg-slate-50 px-6 py-5">

            <p class="text-xs text-slate-400">
                Especialidade #{{ $specialty->id }}
            </p>

            <div class="flex gap-3">

                <a
                    href="{{ route('specialties.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
                >
                    Salvar alterações
                </button>

            </div>

        </div>

    </form>

</div>

@endsection
