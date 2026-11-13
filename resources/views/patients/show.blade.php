@extends('layouts.app')

@section('title', 'Paciente | Vitallis')
@section('header', 'Detalhes do paciente')
@section('subtitle', 'Visualização do cadastro')

@section('content')

<div class="mx-auto max-w-6xl">

    {{-- CABEÇALHO --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <a
                href="{{ route('patients.index') }}"
                class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
            >
                ← Voltar para pacientes
            </a>

            <h1 class="mt-4 text-2xl font-semibold text-slate-900">
                {{ $patient->name }}
            </h1>

            @if($patient->social_name)
                <p class="mt-1 text-sm text-slate-500">
                    Nome social: {{ $patient->social_name }}
                </p>
            @endif
        </div>

        <div class="flex gap-2">
            <a
                href="{{ route('patients.edit', $patient) }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                Editar paciente
            </a>
        </div>

    </div>


    {{-- STATUS --}}
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-4">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Situação do cadastro
                </p>

                <div class="mt-2">
                    @if($patient->active)
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                            Ativo
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                            Inativo
                        </span>
                    @endif
                </div>
            </div>

            <div class="text-right text-sm text-slate-500">
                <p>
                    Código do paciente
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    #{{ $patient->id }}
                </p>
            </div>

        </div>

    </div>


    {{-- IDENTIFICAÇÃO --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informações cadastrais do paciente.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 p-6 md:grid-cols-2 lg:grid-cols-3">

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Nome completo
                </p>

                <p class="mt-1 text-sm font-medium text-slate-900">
                    {{ $patient->name }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Nome social
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->social_name ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    CPF
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->cpf ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    CNS
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->cns ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Data de nascimento
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    @if($patient->birth_date)
                        {{ \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') }}
                    @else
                        Não informado
                    @endif
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Sexo
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    @switch($patient->sex)
                        @case('masculino')
                            Masculino
                            @break

                        @case('feminino')
                            Feminino
                            @break

                        @case('intersexo')
                            Intersexo
                            @break

                        @case('nao_informado')
                            Prefere não informar
                            @break

                        @default
                            Não informado
                    @endswitch
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Nome da mãe
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->mother_name ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Nome do pai
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->father_name ?: 'Não informado' }}
                </p>
            </div>

        </div>

    </div>


    {{-- CONTATO --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Contato
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 p-6 md:grid-cols-3">

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    E-mail
                </p>

                <p class="mt-1 break-all text-sm text-slate-700">
                    {{ $patient->email ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Telefone
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->phone ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Celular
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->mobile_phone ?: 'Não informado' }}
                </p>
            </div>

        </div>

    </div>


    {{-- ENDEREÇO --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Endereço
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 p-6 md:grid-cols-2 lg:grid-cols-4">

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    CEP
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->postal_code ?: 'Não informado' }}
                </p>
            </div>


            <div class="lg:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Logradouro
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->street ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Número
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->number ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Complemento
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->complement ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Bairro
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->district ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Cidade
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->city ?: 'Não informado' }}
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    UF
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->state ?: 'Não informado' }}
                </p>
            </div>

        </div>

    </div>


    {{-- VÍNCULOS ASSISTENCIAIS --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Vínculos assistenciais
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Redes às quais este paciente está vinculado.
            </p>
        </div>

        <div class="p-6">

            @if($patient->networks->isEmpty())

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">
                        Nenhum vínculo assistencial encontrado.
                    </p>
                </div>

            @else

                <div class="space-y-3">

                    @foreach($patient->networks as $network)

                        <div class="flex flex-col gap-3 rounded-lg border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <p class="font-medium text-slate-900">
                                    {{ $network->name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Rede #{{ $network->id }}
                                </p>
                            </div>

                            @if(isset($network->pivot->active))
                                @if($network->pivot->active)
                                    <span class="inline-flex self-start rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 sm:self-auto">
                                        Vínculo ativo
                                    </span>
                                @else
                                    <span class="inline-flex self-start rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 sm:self-auto">
                                        Vínculo inativo
                                    </span>
                                @endif
                            @endif

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>


    {{-- USUÁRIOS VINCULADOS --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Usuários vinculados
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Contas de acesso relacionadas a este paciente.
            </p>
        </div>

        <div class="p-6">

            @if($patient->users->isEmpty())

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">
                        Nenhum usuário vinculado.
                    </p>
                </div>

            @else

                <div class="divide-y divide-slate-100">

                    @foreach($patient->users as $user)

                        <div class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <p class="text-sm font-medium text-slate-900">
                                    {{ $user->name }}
                                </p>

                                <p class="text-sm text-slate-500">
                                    {{ $user->email }}
                                </p>
                            </div>

                            @if($user->active)
                                <span class="inline-flex self-start rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 sm:self-auto">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex self-start rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 sm:self-auto">
                                    Inativo
                                </span>
                            @endif

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </div>


    {{-- INFORMAÇÕES DO REGISTRO --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Informações do registro
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Cadastrado em
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->created_at?->format('d/m/Y H:i') ?? 'Não informado' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    Última atualização
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $patient->updated_at?->format('d/m/Y H:i') ?? 'Não informado' }}
                </p>
            </div>

        </div>

    </div>

</div>

@endsection
