@extends('layouts.app')

@section('title', 'Novo Paciente | Vitallis')
@section('header', 'Novo paciente')
@section('subtitle', 'Cadastro de paciente')

@section('content')

<div class="mx-auto max-w-6xl">

    <div class="mb-6">
        <a
            href="{{ route('patients.index') }}"
            class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
        >
            ← Voltar para pacientes
        </a>

        <h1 class="mt-4 text-2xl font-semibold text-slate-900">
            Cadastrar paciente
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Informe os dados de identificação, contato e vínculo inicial do paciente.
        </p>
    </div>


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
        action="{{ route('patients.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        @csrf


        {{-- VÍNCULO ASSISTENCIAL --}}
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Vínculo assistencial
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Define a rede responsável pelo cadastro inicial do paciente.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            <div>
                <label
                    for="network_id"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Rede *
                </label>

                <select
                    id="network_id"
                    name="network_id"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">
                        Selecione uma rede
                    </option>

                    @foreach($networks as $network)
                        <option
                            value="{{ $network->id }}"
                            @selected(
                                (string) old('network_id')
                                === (string) $network->id
                            )
                        >
                            {{ $network->name }}
                        </option>
                    @endforeach
                </select>

                @error('network_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            <div>
                <label
                    for="origin_unit_id"
                    class="mb-2 block text-sm font-medium text-slate-700"
                >
                    Unidade de origem
                </label>

                <select
                    id="origin_unit_id"
                    name="origin_unit_id"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">
                        Selecione uma unidade
                    </option>

                    @foreach($units as $unit)
                        <option
                            value="{{ $unit->id }}"
                            data-network-id="{{ $unit->network_id }}"
                            @selected(
                                (string) old('origin_unit_id')
                                === (string) $unit->id
                            )
                        >
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-xs text-slate-400">
                    Registra onde ocorreu o vínculo inicial. O paciente pertence à rede, não exclusivamente à unidade.
                </p>

                @error('origin_unit_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>


        {{-- IDENTIFICAÇÃO --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Identificação
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Dados cadastrais do paciente.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">
                    Nome completo *
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name') }}"
                    placeholder="Nome completo do paciente"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="social_name" class="mb-2 block text-sm font-medium text-slate-700">
                    Nome social
                </label>

                <input
                    id="social_name"
                    name="social_name"
                    type="text"
                    maxlength="255"
                    value="{{ old('social_name') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('social_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="cpf" class="mb-2 block text-sm font-medium text-slate-700">
                    CPF
                </label>

                <input
                    id="cpf"
                    name="cpf"
                    type="text"
                    maxlength="14"
                    value="{{ old('cpf') }}"
                    placeholder="000.000.000-00"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('cpf')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="cns" class="mb-2 block text-sm font-medium text-slate-700">
                    CNS
                </label>

                <input
                    id="cns"
                    name="cns"
                    type="text"
                    maxlength="20"
                    value="{{ old('cns') }}"
                    placeholder="Cartão Nacional de Saúde"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('cns')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="birth_date" class="mb-2 block text-sm font-medium text-slate-700">
                    Data de nascimento
                </label>

                <input
                    id="birth_date"
                    name="birth_date"
                    type="date"
                    max="{{ now()->format('Y-m-d') }}"
                    value="{{ old('birth_date') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('birth_date')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="sex" class="mb-2 block text-sm font-medium text-slate-700">
                    Sexo
                </label>

                <select
                    id="sex"
                    name="sex"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">Não informado</option>

                    <option value="masculino" @selected(old('sex') === 'masculino')>
                        Masculino
                    </option>

                    <option value="feminino" @selected(old('sex') === 'feminino')>
                        Feminino
                    </option>

                    <option value="intersexo" @selected(old('sex') === 'intersexo')>
                        Intersexo
                    </option>

                    <option value="nao_informado" @selected(old('sex') === 'nao_informado')>
                        Prefere não informar
                    </option>
                </select>

                @error('sex')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="mother_name" class="mb-2 block text-sm font-medium text-slate-700">
                    Nome da mãe
                </label>

                <input
                    id="mother_name"
                    name="mother_name"
                    type="text"
                    maxlength="255"
                    value="{{ old('mother_name') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('mother_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="father_name" class="mb-2 block text-sm font-medium text-slate-700">
                    Nome do pai
                </label>

                <input
                    id="father_name"
                    name="father_name"
                    type="text"
                    maxlength="255"
                    value="{{ old('father_name') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('father_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>


        {{-- CONTATO --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Contato
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                    E-mail
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    maxlength="255"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="phone" class="mb-2 block text-sm font-medium text-slate-700">
                    Telefone
                </label>

                <input
                    id="phone"
                    name="phone"
                    type="text"
                    maxlength="30"
                    value="{{ old('phone') }}"
                    placeholder="(71) 0000-0000"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="mobile_phone" class="mb-2 block text-sm font-medium text-slate-700">
                    Celular
                </label>

                <input
                    id="mobile_phone"
                    name="mobile_phone"
                    type="text"
                    maxlength="30"
                    value="{{ old('mobile_phone') }}"
                    placeholder="(71) 99999-9999"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >

                @error('mobile_phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>


        {{-- ENDEREÇO --}}
        <div class="border-y border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Endereço
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-12">

            <div class="md:col-span-3">
                <label for="postal_code" class="mb-2 block text-sm font-medium text-slate-700">
                    CEP
                </label>

                <input
                    id="postal_code"
                    name="postal_code"
                    type="text"
                    maxlength="10"
                    value="{{ old('postal_code') }}"
                    placeholder="00000-000"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>


            <div class="md:col-span-7">
                <label for="street" class="mb-2 block text-sm font-medium text-slate-700">
                    Logradouro
                </label>

                <input
                    id="street"
                    name="street"
                    type="text"
                    maxlength="255"
                    value="{{ old('street') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>


            <div class="md:col-span-2">
                <label for="number" class="mb-2 block text-sm font-medium text-slate-700">
                    Número
                </label>

                <input
                    id="number"
                    name="number"
                    type="text"
                    maxlength="20"
                    value="{{ old('number') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>


            <div class="md:col-span-4">
                <label for="complement" class="mb-2 block text-sm font-medium text-slate-700">
                    Complemento
                </label>

                <input
                    id="complement"
                    name="complement"
                    type="text"
                    maxlength="255"
                    value="{{ old('complement') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>


            <div class="md:col-span-3">
                <label for="district" class="mb-2 block text-sm font-medium text-slate-700">
                    Bairro
                </label>

                <input
                    id="district"
                    name="district"
                    type="text"
                    maxlength="255"
                    value="{{ old('district') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>


            <div class="md:col-span-4">
                <label for="city" class="mb-2 block text-sm font-medium text-slate-700">
                    Cidade
                </label>

                <input
                    id="city"
                    name="city"
                    type="text"
                    maxlength="255"
                    value="{{ old('city') }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>


            <div class="md:col-span-1">
                <label for="state" class="mb-2 block text-sm font-medium text-slate-700">
                    UF
                </label>

                <input
                    id="state"
                    name="state"
                    type="text"
                    maxlength="2"
                    value="{{ old('state') }}"
                    placeholder="BA"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm uppercase outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                >
            </div>

        </div>


        {{-- RODAPÉ --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end">

            <a
                href="{{ route('patients.index') }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Cancelar
            </a>

            <button
                type="submit"
                @disabled($networks->isEmpty())
                class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"
            >
                Cadastrar paciente
            </button>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const networkSelect = document.getElementById('network_id');
    const unitSelect = document.getElementById('origin_unit_id');

    if (!networkSelect || !unitSelect) {
        return;
    }

    const unitOptions = Array.from(unitSelect.options);

    function filterUnits() {
        const networkId = networkSelect.value;
        const currentValue = unitSelect.value;

        unitOptions.forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const belongs =
                networkId !== '' &&
                option.dataset.networkId === networkId;

            option.hidden = !belongs;
            option.disabled = !belongs;
        });

        const currentOption = unitOptions.find(
            option => option.value === currentValue
        );

        if (
            currentOption &&
            currentOption.value &&
            currentOption.disabled
        ) {
            unitSelect.value = '';
        }
    }

    networkSelect.addEventListener('change', filterUnits);

    filterUnits();
});
</script>

@endsection
