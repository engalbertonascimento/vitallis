@extends('layouts.app')

@section('title', 'Pacientes | Vitallis')
@section('header', 'Pacientes')
@section('subtitle', 'Cadastro e gerenciamento de pacientes')

@section('content')

<div class="space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Pacientes
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Consulte os pacientes disponíveis no seu contexto de acesso.
            </p>
        </div>

        @if(auth()->user()->hasPermission('patients.create'))

            <a
                href="{{ route('patients.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Novo paciente
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


    {{-- Pesquisa / filtro --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

        <form
            method="GET"
            action="{{ route('patients.index') }}"
            class="grid grid-cols-1 gap-3 lg:grid-cols-12"
        >

            {{-- Pesquisa --}}
            <div class="lg:col-span-6">

                <label
                    for="search"
                    class="sr-only"
                >
                    Pesquisar paciente
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Nome, nome social, CPF, CNS, telefone ou e-mail..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

            </div>


            {{-- Rede --}}
            <div class="lg:col-span-3">

                <label
                    for="network_id"
                    class="sr-only"
                >
                    Rede
                </label>

                <select
                    id="network_id"
                    name="network_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                    <option value="">
                        Todas as redes acessíveis
                    </option>

                    @foreach($networks as $network)

                        <option
                            value="{{ $network->id }}"
                            @selected(
                                (string) $networkId
                                ===
                                (string) $network->id
                            )
                        >
                            {{ $network->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Ações --}}
            <div class="flex gap-2 lg:col-span-3">

                <button
                    type="submit"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Pesquisar
                </button>

                @if($search !== '' || $networkId)

                    <a
                        href="{{ route('patients.index') }}"
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
                            Paciente
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Documento
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Nascimento
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Rede
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

                    @forelse($patients as $patient)

                        <tr class="hover:bg-slate-50">

                            {{-- Paciente --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700">
                                        {{ strtoupper(substr($patient->name, 0, 1)) }}
                                    </div>

                                    <div>

                                        <a
                                            href="{{ route('patients.show', $patient) }}"
                                            class="font-medium text-slate-900 hover:text-emerald-700"
                                        >
                                            {{ $patient->name }}
                                        </a>

                                        @if($patient->social_name)

                                            <p class="mt-1 text-sm text-slate-500">
                                                Nome social:
                                                {{ $patient->social_name }}
                                            </p>

                                        @endif


                                        @if($patient->mobile_phone)

                                            <p class="mt-1 text-xs text-slate-400">
                                                {{ $patient->mobile_phone }}
                                            </p>

                                        @elseif($patient->phone)

                                            <p class="mt-1 text-xs text-slate-400">
                                                {{ $patient->phone }}
                                            </p>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- Documento --}}
                            <td class="px-6 py-4">

                                @if($patient->cpf)

                                    <p class="text-sm text-slate-700">
                                        CPF: {{ $patient->cpf }}
                                    </p>

                                @endif

                                @if($patient->cns)

                                    <p class="mt-1 text-xs text-slate-500">
                                        CNS: {{ $patient->cns }}
                                    </p>

                                @endif

                                @if(!$patient->cpf && !$patient->cns)

                                    <span class="text-sm text-slate-400">
                                        Não informado
                                    </span>

                                @endif

                            </td>


                            {{-- Nascimento --}}
                            <td class="px-6 py-4">

                                @if($patient->birth_date)

                                    <p class="text-sm text-slate-700">
                                        {{ $patient->birth_date->format('d/m/Y') }}
                                    </p>

                                @else

                                    <span class="text-sm text-slate-400">
                                        Não informado
                                    </span>

                                @endif

                            </td>


                            {{-- Redes --}}
                            <td class="px-6 py-4">

                                @forelse($patient->networks as $network)

                                    <span class="mb-1 mr-1 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                        {{ $network->name }}
                                    </span>

                                @empty

                                    <span class="text-sm text-slate-400">
                                        Sem vínculo
                                    </span>

                                @endforelse

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @if($patient->active)

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

                                    <a
                                        href="{{ route('patients.show', $patient) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                    >
                                        Visualizar
                                    </a>


                                    @if(auth()->user()->hasPermission('patients.update'))

                                        <a
                                            href="{{ route('patients.edit', $patient) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Editar
                                        </a>


                                        <form
                                            method="POST"
                                            action="{{ route('patients.toggle-status', $patient) }}"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg px-3 py-2 text-sm font-medium transition
                                                {{
                                                    $patient->active
                                                        ? 'text-red-600 hover:bg-red-50'
                                                        : 'text-emerald-700 hover:bg-emerald-50'
                                                }}"
                                            >
                                                {{ $patient->active ? 'Desativar' : 'Ativar' }}
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-16 text-center"
                            >

                                <p class="font-medium text-slate-700">
                                    Nenhum paciente encontrado
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Nenhum paciente está disponível no seu contexto de acesso.
                                </p>


                                @if(auth()->user()->hasPermission('patients.create'))

                                    <a
                                        href="{{ route('patients.create') }}"
                                        class="mt-5 inline-flex rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                                    >
                                        Cadastrar primeiro paciente
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Paginação --}}
        @if($patients->hasPages())

            <div class="border-t border-slate-200 px-6 py-4">
                {{ $patients->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
