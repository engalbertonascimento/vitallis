@extends('layouts.app')

@section('title', 'Locais de Atendimento | Vitallis')
@section('header', 'Locais de Atendimento')
@section('subtitle', 'Estrutura física e assistencial da unidade')

@section('content')

@php
    $typeColors = [
        'building' => 'bg-slate-100 text-slate-700',
        'block' => 'bg-blue-50 text-blue-700',
        'floor' => 'bg-violet-50 text-violet-700',
        'wing' => 'bg-indigo-50 text-indigo-700',
        'sector' => 'bg-cyan-50 text-cyan-700',
        'room' => 'bg-amber-50 text-amber-700',
        'office' => 'bg-emerald-50 text-emerald-700',
        'bed' => 'bg-rose-50 text-rose-700',
        'other' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h1 class="text-2xl font-semibold text-slate-900">
                Locais de Atendimento
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Gerencie blocos, andares, alas, setores, salas, consultórios e demais locais da unidade.
            </p>

        </div>

        @if(auth()->user()->hasPermission('service_locations.manage'))

            <a
                href="{{ route('service-locations.create') }}"
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Novo local
            </a>

        @endif

    </div>


    {{-- Unidade atual --}}
    <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">

        <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
            Unidade
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
            Os locais abaixo pertencem exclusivamente à unidade atualmente selecionada.
        </p>

    </section>


    {{-- Erros --}}
    @if($errors->any())

        <div class="rounded-xl border border-red-200 bg-red-50 p-4">

            @foreach($errors->all() as $error)

                <p class="text-sm text-red-700">
                    {{ $error }}
                </p>

            @endforeach

        </div>

    @endif


    {{-- Filtros --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('service-locations.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-12"
        >

            <div class="md:col-span-6">

                <label
                    for="search"
                    class="sr-only"
                >
                    Pesquisar local
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Pesquisar por nome, código ou descrição..."
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

            </div>


            <div class="md:col-span-3">

                <label
                    for="type"
                    class="sr-only"
                >
                    Tipo
                </label>

                <select
                    id="type"
                    name="type"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                    <option value="">
                        Todos os tipos
                    </option>

                    @foreach($types as $typeKey => $typeLabel)

                        <option
                            value="{{ $typeKey }}"
                            @selected($type === $typeKey)
                        >
                            {{ $typeLabel }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="flex gap-2 md:col-span-3">

                <button
                    type="submit"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Pesquisar
                </button>

                @if($search !== '' || $type !== '')

                    <a
                        href="{{ route('service-locations.index') }}"
                        class="flex items-center justify-center rounded-lg px-3 py-3 text-sm text-slate-500 hover:text-slate-900"
                    >
                        Limpar
                    </a>

                @endif

            </div>

        </form>

    </section>


    {{-- Lista --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="font-semibold text-slate-900">
                Estrutura cadastrada
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Os relacionamentos permitem organizar os locais hierarquicamente.
            </p>

        </div>


        @if($locations->isEmpty())

            <div class="px-6 py-16 text-center">

                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 font-semibold text-emerald-700">
                    L
                </div>

                <h3 class="mt-4 font-medium text-slate-800">
                    Nenhum local cadastrado
                </h3>

                <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">
                    Comece cadastrando a estrutura física da unidade.
                </p>


                @if(auth()->user()->hasPermission('service_locations.manage'))

                    <a
                        href="{{ route('service-locations.create') }}"
                        class="mt-5 inline-flex rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Cadastrar primeiro local
                    </a>

                @endif

            </div>

        @else

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Local
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Tipo
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Código
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Local superior
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @foreach($locations as $location)

                            <tr class="hover:bg-slate-50">

                                <td class="px-6 py-4">

                                    <p class="font-medium text-slate-900">
                                        {{ $location->name }}
                                    </p>

                                    @if($location->description)

                                        <p class="mt-1 max-w-md text-sm text-slate-500">
                                            {{ $location->description }}
                                        </p>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $typeColors[$location->type] ?? 'bg-slate-100 text-slate-600' }}"
                                    >
                                        {{ $types[$location->type] ?? $location->type }}
                                    </span>

                                </td>


                                <td class="px-6 py-4">

                                    @if($location->code)

                                        <span class="font-mono text-sm text-slate-700">
                                            {{ $location->code }}
                                        </span>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            Não informado
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    @if($location->parent)

                                        <p class="text-sm font-medium text-slate-700">
                                            {{ $location->parent->name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $types[$location->parent->type] ?? $location->parent->type }}
                                        </p>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            Raiz da unidade
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    @if($location->active)

                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Ativo
                                        </span>

                                    @else

                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            Inativo
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-4">

                                    @if(auth()->user()->hasPermission('service_locations.manage'))

                                        <div class="flex items-center justify-end gap-2">

                                            <a
                                                href="{{ route('service-locations.edit', $location) }}"
                                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('service-locations.toggle-status', $location) }}"
                                            >

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg px-3 py-2 text-sm font-medium transition
                                                        {{
                                                            $location->active
                                                                ? 'text-red-600 hover:bg-red-50'
                                                                : 'text-emerald-700 hover:bg-emerald-50'
                                                        }}"
                                                >
                                                    {{ $location->active ? 'Desativar' : 'Ativar' }}
                                                </button>

                                            </form>

                                        </div>

                                    @else

                                        <div class="text-right text-sm text-slate-400">
                                            Somente leitura
                                        </div>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            @if($locations->hasPages())

                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $locations->links() }}
                </div>

            @endif

        @endif

    </section>

</div>

@endsection