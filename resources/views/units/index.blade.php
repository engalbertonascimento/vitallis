@extends('layouts.app')

@section('title', 'Unidades | Vitallis')
@section('header', 'Unidades')
@section('subtitle', 'Gerenciamento das unidades de atendimento')

@section('content')

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Unidades
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Gerencie as unidades vinculadas às redes do Vitallis.
            </p>
        </div>

        @if(auth()->user()->hasPermission('unit.manage'))
            <a
                href="{{ route('units.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Nova unidade
            </a>
        @endif

    </div>


    {{-- Filtros --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('units.index') }}"
            class="grid grid-cols-1 gap-3 lg:grid-cols-12"
        >

            {{-- Pesquisa --}}
            <div class="lg:col-span-6">

                <label for="search" class="sr-only">
                    Pesquisar unidade
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Nome, código, documento, cidade ou e-mail..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

            </div>


            {{-- Rede --}}
            <div class="lg:col-span-3">

                <label for="network_id" class="sr-only">
                    Rede
                </label>

                <select
                    id="network_id"
                    name="network_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                    <option value="">
                        Todas as redes
                    </option>

                    @foreach($networks as $network)
                        <option
                            value="{{ $network->id }}"
                            @selected((string) $networkId === (string) $network->id)
                        >
                            {{ $network->name }}
                        </option>
                    @endforeach

                </select>

            </div>


            <div class="flex gap-2 lg:col-span-3">

                <button
                    type="submit"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Pesquisar
                </button>

                @if($search !== '' || $networkId)
                    <a
                        href="{{ route('units.index') }}"
                        class="flex items-center justify-center rounded-lg px-3 py-3 text-sm text-slate-500 hover:text-slate-900"
                    >
                        Limpar
                    </a>
                @endif

            </div>

        </form>

    </div>


    {{-- Tabela --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Unidade
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Rede
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Localização
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Contato
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Ações
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse($units as $unit)

                        <tr class="hover:bg-slate-50">

                            {{-- Unidade --}}
                            <td class="px-6 py-4">

                                <p class="font-medium text-slate-900">
                                    {{ $unit->name }}
                                </p>

                                @if($unit->code)
                                    <p class="mt-1 text-xs text-slate-500">
                                        Código: {{ $unit->code }}
                                    </p>
                                @endif

                                @if($unit->document)
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $unit->document }}
                                    </p>
                                @endif

                            </td>


                            {{-- Rede --}}
                            <td class="px-6 py-4">

                                <p class="text-sm font-medium text-slate-700">
                                    {{ $unit->network->name }}
                                </p>

                            </td>


                            {{-- Localização --}}
                            <td class="px-6 py-4">

                                @if($unit->city || $unit->state)

                                    <p class="text-sm text-slate-700">
                                        {{ $unit->city ?: 'Não informado' }}
                                        @if($unit->state)
                                            / {{ $unit->state }}
                                        @endif
                                    </p>

                                    @if($unit->district)
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $unit->district }}
                                        </p>
                                    @endif

                                @else

                                    <span class="text-sm text-slate-400">
                                        Não informado
                                    </span>

                                @endif

                            </td>


                            {{-- Contato --}}
                            <td class="px-6 py-4">

                                @if($unit->email)
                                    <p class="text-sm text-slate-700">
                                        {{ $unit->email }}
                                    </p>
                                @endif

                                @if($unit->phone)
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $unit->phone }}
                                    </p>
                                @endif

                                @if(!$unit->email && !$unit->phone)
                                    <span class="text-sm text-slate-400">
                                        Não informado
                                    </span>
                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @if($unit->active)

                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Ativa
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Inativa
                                    </span>

                                @endif

                            </td>


                            {{-- Ações --}}
                            <td class="px-6 py-4">

                                @if(auth()->user()->hasPermission('unit.manage'))

                                    <div class="flex items-center justify-end gap-2">

                                        <a
                                            href="{{ route('units.edit', $unit) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('units.toggle-status', $unit) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg px-3 py-2 text-sm font-medium transition
                                                    {{
                                                        $unit->active
                                                            ? 'text-red-600 hover:bg-red-50'
                                                            : 'text-emerald-700 hover:bg-emerald-50'
                                                    }}"
                                            >
                                                {{ $unit->active ? 'Desativar' : 'Ativar' }}
                                            </button>

                                        </form>

                                    </div>

                                @else

                                    <span class="block text-right text-sm text-slate-400">
                                        Somente leitura
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-16 text-center"
                            >

                                <p class="font-medium text-slate-700">
                                    Nenhuma unidade cadastrada
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Cadastre uma unidade e vincule-a a uma rede.
                                </p>

                                @if(auth()->user()->hasPermission('unit.manage'))

                                    <a
                                        href="{{ route('units.create') }}"
                                        class="mt-5 inline-flex rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
                                    >
                                        Cadastrar primeira unidade
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($units->hasPages())

            <div class="border-t border-slate-200 px-6 py-4">

                {{ $units->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
