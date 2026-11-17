@extends('layouts.app')

@section('title', 'Configurar Permissões | Vitallis')
@section('header', 'Funções e Permissões')
@section('subtitle', 'Configuração de permissões por unidade')

@section('content')

@php
    $groupLabels = [
        'dashboard' => 'Dashboard',
        'patients' => 'Pacientes',
        'appointments' => 'Agendamentos',
        'schedules' => 'Grades de Agenda',
        'medical_records' => 'Prontuário',
        'prescriptions' => 'Prescrições',
        'exams' => 'Exames',
        'users' => 'Usuários',
        'roles' => 'Funções',
        'permissions' => 'Permissões',
        'unit_permissions' => 'Permissões por Unidade',
        'user_permissions' => 'Permissões Especiais de Usuários',
        'reports' => 'Relatórios',
        'network' => 'Redes',
        'unit' => 'Unidades',
        'audit' => 'Auditoria',
    ];
@endphp


<div class="space-y-6">

    {{-- =========================================================
         CABEÇALHO
    ========================================================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        <div>

            <a
                href="{{ route('roles.index', [
                    'network_id' => $unit->network_id,
                    'unit_id' => $unit->id,
                ]) }}"
                class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
            >
                ← Voltar para funções
            </a>

            <h1 class="mt-4 text-2xl font-semibold text-slate-900">
                {{ $role->name }}
            </h1>

            @if($role->description)

                <p class="mt-1 text-sm text-slate-500">
                    {{ $role->description }}
                </p>

            @endif

        </div>


        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3">

            <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
                Unidade
            </p>

            <p class="mt-1 font-semibold text-emerald-900">
                {{ $unit->name }}
            </p>

            @if($unit->network)

                <p class="mt-0.5 text-sm text-emerald-700">
                    {{ $unit->network->name }}
                </p>

            @endif

        </div>

    </div>


    {{-- =========================================================
         LEGENDA
    ========================================================== --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">

        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

            <div class="rounded-lg bg-slate-50 p-4">

                <p class="font-medium text-slate-900">
                    Herdar
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Utiliza a permissão padrão definida para a função.
                </p>

            </div>


            <div class="rounded-lg bg-emerald-50 p-4">

                <p class="font-medium text-emerald-800">
                    Permitir
                </p>

                <p class="mt-1 text-sm text-emerald-700">
                    Permite explicitamente a ação nesta unidade.
                </p>

            </div>


            <div class="rounded-lg bg-red-50 p-4">

                <p class="font-medium text-red-800">
                    Negar
                </p>

                <p class="mt-1 text-sm text-red-700">
                    Bloqueia explicitamente a ação nesta unidade.
                </p>

            </div>

        </div>

    </section>


    {{-- =========================================================
         OVERRIDES DA UNIDADE
    ========================================================== --}}
    <form
        method="POST"
        action="{{ route('roles.update-unit-permissions', $role) }}"
        class="space-y-6"
    >

        @csrf
        @method('PUT')

        <input
            type="hidden"
            name="unit_id"
            value="{{ $unit->id }}"
        >


        @foreach($permissionGroups as $group => $groupPermissions)

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                {{-- Grupo --}}
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">

                    <h2 class="font-semibold text-slate-900">
                        {{ $groupLabels[$group] ?? ucfirst(str_replace('_', ' ', $group)) }}
                    </h2>

                </div>


                {{-- Cabeçalho da matriz --}}
                <div class="hidden grid-cols-12 gap-4 border-b border-slate-200 bg-white px-6 py-3 md:grid">

                    <div class="col-span-6">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Permissão
                        </p>

                    </div>

                    <div class="col-span-2">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Padrão
                        </p>

                    </div>

                    <div class="col-span-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Nesta unidade
                        </p>

                    </div>

                </div>


                <div class="divide-y divide-slate-100">

                    @foreach($groupPermissions as $permission)

                        @php
                            $isDefaultAllowed = in_array(
                                $permission->id,
                                $defaultPermissionIds,
                                true
                            );

                            $override = $overrides->get(
                                $permission->id
                            );

                            if ($override === null) {
                                $overrideState = 'inherit';
                            } else {
                                $overrideState = (bool) $override->allowed
                                    ? 'allow'
                                    : 'deny';
                            }
                        @endphp


                        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-12 md:items-center">

                            {{-- Permissão --}}
                            <div class="md:col-span-6">

                                <p class="text-sm font-medium text-slate-900">
                                    {{ $permission->name }}
                                </p>

                                <p class="mt-1 font-mono text-xs text-slate-400">
                                    {{ $permission->slug }}
                                </p>

                            </div>


                            {{-- Padrão --}}
                            <div class="md:col-span-2">

                                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400 md:hidden">
                                    Padrão
                                </p>

                                @if($isDefaultAllowed)

                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Permitido
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Negado
                                    </span>

                                @endif

                            </div>


                            {{-- Override --}}
                            <div class="md:col-span-4">

                                <label
                                    for="permission_{{ $permission->id }}"
                                    class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400 md:hidden"
                                >
                                    Nesta unidade
                                </label>

                                <select
                                    id="permission_{{ $permission->id }}"
                                    name="permissions[{{ $permission->id }}]"
                                    class="
                                        w-full rounded-lg border px-4 py-2.5 text-sm font-medium outline-none transition
                                        focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100

                                        @if($overrideState === 'allow')
                                            border-emerald-300 bg-emerald-50 text-emerald-800
                                        @elseif($overrideState === 'deny')
                                            border-red-300 bg-red-50 text-red-800
                                        @else
                                            border-slate-300 bg-white text-slate-700
                                        @endif
                                    "
                                >

                                    <option
                                        value="inherit"
                                        @selected($overrideState === 'inherit')
                                    >
                                        Herdar
                                    </option>

                                    <option
                                        value="allow"
                                        @selected($overrideState === 'allow')
                                    >
                                        Permitir
                                    </option>

                                    <option
                                        value="deny"
                                        @selected($overrideState === 'deny')
                                    >
                                        Negar
                                    </option>

                                </select>

                            </div>

                        </div>

                    @endforeach

                </div>

            </section>

        @endforeach


        {{-- =====================================================
             SALVAR
        ====================================================== --}}
        <div class="sticky bottom-4 z-20">

            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-sm font-medium text-slate-900">
                        Configuração específica da unidade
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Apenas diferenças em relação ao padrão serão armazenadas.
                    </p>

                </div>


                <div class="flex gap-3">

                    <a
                        href="{{ route('roles.index', [
                            'network_id' => $unit->network_id,
                            'unit_id' => $unit->id,
                        ]) }}"
                        class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Salvar permissões
                    </button>

                </div>

            </div>

        </div>

    </form>


    {{-- =========================================================
         PERMISSÕES PADRÃO DA FUNÇÃO
    ========================================================== --}}
    @if(auth()->user()->hasPermission('roles.manage'))

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="font-semibold text-slate-900">
                    Permissões padrão da função
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Essa configuração será utilizada em qualquer unidade que esteja configurada como Herdar.
                </p>

            </div>


            <details>

                <summary class="cursor-pointer px-6 py-4 text-sm font-medium text-emerald-700 hover:bg-slate-50">
                    Alterar permissões padrão
                </summary>


                <form
                    method="POST"
                    action="{{ route('roles.update-defaults', $role) }}"
                    class="border-t border-slate-200"
                >

                    @csrf
                    @method('PUT')


                    <div class="space-y-6 p-6">

                        @foreach($permissionGroups as $group => $groupPermissions)

                            <div>

                                <h3 class="mb-3 text-sm font-semibold text-slate-900">
                                    {{ $groupLabels[$group] ?? ucfirst(str_replace('_', ' ', $group)) }}
                                </h3>


                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                                    @foreach($groupPermissions as $permission)

                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50"
                                        >

                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->id }}"
                                                @checked(
                                                    in_array(
                                                        $permission->id,
                                                        $defaultPermissionIds,
                                                        true
                                                    )
                                                )
                                                class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600"
                                            >

                                            <span>

                                                <span class="block text-sm font-medium text-slate-800">
                                                    {{ $permission->name }}
                                                </span>

                                                <span class="mt-1 block font-mono text-xs text-slate-400">
                                                    {{ $permission->slug }}
                                                </span>

                                            </span>

                                        </label>

                                    @endforeach

                                </div>

                            </div>

                        @endforeach

                    </div>


                    <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-4">

                        <button
                            type="submit"
                            class="rounded-lg bg-slate-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-900"
                        >
                            Atualizar padrão da função
                        </button>

                    </div>

                </form>

            </details>

        </section>

    @endif

</div>

@endsection
