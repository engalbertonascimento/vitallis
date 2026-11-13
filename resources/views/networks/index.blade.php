@extends('layouts.app')

@section('title', 'Redes | Vitallis')
@section('header', 'Redes')
@section('subtitle', 'Gerenciamento das redes cadastradas')

@section('content')

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Redes
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Gerencie as organizações que utilizam a plataforma Vitallis.
            </p>
        </div>

        @if(auth()->user()->hasPermission('network.manage'))
            <a
                href="{{ route('networks.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Nova rede
            </a>
        @endif

    </div>


    {{-- Filtro --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('networks.index') }}"
            class="flex flex-col gap-3 sm:flex-row"
        >

            <div class="flex-1">

                <label for="search" class="sr-only">
                    Pesquisar rede
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Pesquisar por nome, razão social, documento ou e-mail..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

            </div>

            <button
                type="submit"
                class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                Pesquisar
            </button>

            @if($search !== '')
                <a
                    href="{{ route('networks.index') }}"
                    class="flex items-center justify-center rounded-lg px-4 py-3 text-sm text-slate-500 hover:text-slate-900"
                >
                    Limpar
                </a>
            @endif

        </form>

    </div>


    {{-- Tabela --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">

                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Rede
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Documento
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

                    @forelse($networks as $network)

                        <tr class="hover:bg-slate-50">

                            <td class="px-6 py-4">

                                <p class="font-medium text-slate-900">
                                    {{ $network->name }}
                                </p>

                                @if($network->legal_name)
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $network->legal_name }}
                                    </p>
                                @endif

                            </td>


                            <td class="px-6 py-4 text-sm text-slate-600">

                                {{ $network->document ?: 'Não informado' }}

                            </td>


                            <td class="px-6 py-4">

                                @if($network->email)
                                    <p class="text-sm text-slate-700">
                                        {{ $network->email }}
                                    </p>
                                @endif

                                @if($network->phone)
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $network->phone }}
                                    </p>
                                @endif

                                @if(!$network->email && !$network->phone)
                                    <span class="text-sm text-slate-400">
                                        Não informado
                                    </span>
                                @endif

                            </td>


                            <td class="px-6 py-4">

                                @if($network->active)

                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Ativa
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Inativa
                                    </span>

                                @endif

                            </td>


                            <td class="px-6 py-4">

                                @if(auth()->user()->hasPermission('network.manage'))

                                    <div class="flex items-center justify-end gap-2">

                                        <a
                                            href="{{ route('networks.edit', $network) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('networks.toggle-status', $network) }}"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg px-3 py-2 text-sm font-medium transition
                                                {{
                                                    $network->active
                                                        ? 'text-red-600 hover:bg-red-50'
                                                        : 'text-emerald-700 hover:bg-emerald-50'
                                                }}"
                                            >
                                                {{ $network->active ? 'Desativar' : 'Ativar' }}
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
                                colspan="5"
                                class="px-6 py-16 text-center"
                            >

                                <p class="font-medium text-slate-700">
                                    Nenhuma rede cadastrada
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Cadastre a primeira rede para começar a estruturar as unidades do Vitallis.
                                </p>

                                @if(auth()->user()->hasPermission('network.manage'))

                                    <a
                                        href="{{ route('networks.create') }}"
                                        class="mt-5 inline-flex rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
                                    >
                                        Cadastrar primeira rede
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Paginação --}}
        @if($networks->hasPages())

            <div class="border-t border-slate-200 px-6 py-4">

                {{ $networks->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
