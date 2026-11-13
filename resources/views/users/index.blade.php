@extends('layouts.app')

@section('title', 'Usuários | Vitallis')
@section('header', 'Usuários')
@section('subtitle', 'Gerenciamento de usuários e acessos')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Usuários
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Gerencie os usuários autorizados a acessar o Vitallis.
            </p>
        </div>

        @if(auth()->user()->hasPermission('users.create'))
            <a
                href="{{ route('users.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Novo usuário
            </a>
        @endif

    </div>


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


    {{-- Pesquisa --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('users.index') }}"
            class="flex flex-col gap-3 sm:flex-row"
        >

            <div class="flex-1">

                <label for="search" class="sr-only">
                    Pesquisar usuário
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Pesquisar por nome ou e-mail..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
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
                    href="{{ route('users.index') }}"
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
                            Usuário
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Função
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Contexto
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

                    @forelse($users as $item)

                        @php
                            $activeRole = $item->roles
                                ->first(fn ($role) => (bool) $role->pivot->active);
                        @endphp

                        <tr class="hover:bg-slate-50">

                            {{-- Usuário --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700">
                                        {{ strtoupper(substr($item->name, 0, 1)) }}
                                    </div>

                                    <div>

                                        <p class="font-medium text-slate-900">
                                            {{ $item->name }}
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $item->email }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- Função --}}
                            <td class="px-6 py-4">

                                @if($activeRole)

                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                        {{ $activeRole->name }}
                                    </span>

                                @else

                                    <span class="text-sm text-slate-400">
                                        Sem função
                                    </span>

                                @endif

                            </td>


                            {{-- Contexto --}}
                            <td class="px-6 py-4">

                                @if($activeRole)

                                    @if($activeRole->slug === 'vitallis-admin')

                                        <p class="text-sm font-medium text-slate-700">
                                            Plataforma Vitallis
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Acesso global
                                        </p>

                                    @else

                                        <p class="text-sm text-slate-700">
                                            Rede:
                                            {{ $activeRole->pivot->network_id ?? 'Não definida' }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Unidade:
                                            {{ $activeRole->pivot->unit_id ?? 'Todas' }}
                                        </p>

                                    @endif

                                @else

                                    <span class="text-sm text-slate-400">
                                        Nenhum contexto ativo
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @if($item->active)

                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Ativo
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Inativo
                                    </span>

                                @endif

                            </td>


                            {{-- Ações --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    @if(auth()->user()->hasPermission('users.update'))

                                        <a
                                            href="{{ route('users.edit', $item) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>

                                        @if(auth()->id() !== $item->id)

                                            <form
                                                method="POST"
                                                action="{{ route('users.toggle-status', $item) }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg px-3 py-2 text-sm font-medium transition
                                                    {{
                                                        $item->active
                                                            ? 'text-red-600 hover:bg-red-50'
                                                            : 'text-emerald-700 hover:bg-emerald-50'
                                                    }}"
                                                >
                                                    {{ $item->active ? 'Desativar' : 'Ativar' }}
                                                </button>

                                            </form>

                                        @endif

                                    @else

                                        <span class="text-sm text-slate-400">
                                            Somente leitura
                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-6 py-16 text-center"
                            >

                                <p class="font-medium text-slate-700">
                                    Nenhum usuário encontrado
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Cadastre um usuário para conceder acesso ao Vitallis.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($users->hasPages())

            <div class="border-t border-slate-200 px-6 py-4">
                {{ $users->links() }}
            </div>

        @endif

    </div>

</div>

@endsection