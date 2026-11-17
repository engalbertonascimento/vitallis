@extends('layouts.app')

@section('title', 'Especialidades | Vitallis')
@section('header', 'Especialidades')
@section('subtitle', 'Cadastro padronizado de especialidades')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Especialidades
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Gerencie as especialidades utilizadas nas agendas e atendimentos.
            </p>
        </div>

        @if(auth()->user()->hasPermission('specialties.manage'))
            <a
                href="{{ route('specialties.create') }}"
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
            >
                Nova especialidade
            </a>
        @endif

    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('specialties.index') }}"
            class="flex flex-col gap-3 sm:flex-row"
        >
            <input
                name="search"
                type="text"
                value="{{ $search }}"
                placeholder="Pesquisar por nome, código ou descrição..."
                class="flex-1 rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >

            <button
                type="submit"
                class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Pesquisar
            </button>

            @if($search !== '')
                <a
                    href="{{ route('specialties.index') }}"
                    class="flex items-center justify-center px-4 py-3 text-sm text-slate-500"
                >
                    Limpar
                </a>
            @endif
        </form>

    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                            Especialidade
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                            Código
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-500">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($specialties as $specialty)

                        <tr class="hover:bg-slate-50">

                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">
                                    {{ $specialty->name }}
                                </p>

                                @if($specialty->description)
                                    <p class="mt-1 max-w-xl text-sm text-slate-500">
                                        {{ $specialty->description }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $specialty->code ?: 'Não informado' }}
                            </td>

                            <td class="px-6 py-4">

                                @if($specialty->active)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Ativa
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Inativa
                                    </span>
                                @endif

                            </td>

                            <td class="px-6 py-4">

                                @if(auth()->user()->hasPermission('specialties.manage'))

                                    <div class="flex justify-end gap-2">

                                        <a
                                            href="{{ route('specialties.edit', $specialty) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('specialties.toggle-status', $specialty) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg px-3 py-2 text-sm font-medium {{ $specialty->active ? 'text-red-600 hover:bg-red-50' : 'text-emerald-700 hover:bg-emerald-50' }}"
                                            >
                                                {{ $specialty->active ? 'Desativar' : 'Ativar' }}
                                            </button>
                                        </form>

                                    </div>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">

                                <p class="font-medium text-slate-700">
                                    Nenhuma especialidade cadastrada
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Cadastre especialidades para utilizá-las nas grades de agenda.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($specialties->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $specialties->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
