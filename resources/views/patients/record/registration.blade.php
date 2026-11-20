<div class="p-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h2 class="text-lg font-semibold text-slate-900">
                Ficha cadastral
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informações administrativas e de identificação do paciente.
            </p>

        </div>


        @if(auth()->user()->hasPermission('patients.update'))

            <a
                href="{{ route('patients.edit', $patient) }}"
                class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
            >
                Editar cadastro
            </a>

        @endif

    </div>


    <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

        @foreach([
            'Nome completo' =>
                $patient->name,

            'Nome social' =>
                $patient->social_name,

            'CPF' =>
                $patient->cpf,

            'CNS' =>
                $patient->cns,

            'Nascimento' =>
                $patient->birth_date?->format('d/m/Y'),

            'Sexo' =>
                $patient->sex,

            'Nome da mãe' =>
                $patient->mother_name,

            'Nome do pai' =>
                $patient->father_name,

            'E-mail' =>
                $patient->email,

            'Telefone' =>
                $patient->phone,

            'Celular' =>
                $patient->mobile_phone,

            'CEP' =>
                $patient->postal_code,
        ] as $label => $value)

            <div class="rounded-lg border border-slate-200 p-4">

                <p class="text-xs uppercase tracking-wide text-slate-400">
                    {{ $label }}
                </p>

                <p class="mt-2 text-sm font-medium text-slate-800">
                    {{ $value ?: 'Não informado' }}
                </p>

            </div>

        @endforeach

    </div>


    <div class="mt-5 rounded-xl border border-slate-200 p-5">

        <p class="text-xs uppercase tracking-wide text-slate-400">
            Endereço
        </p>

        <p class="mt-2 text-sm font-medium text-slate-800">

            {{ $patient->street ?: 'Não informado' }}

            @if($patient->number)
                , {{ $patient->number }}
            @endif

            @if($patient->complement)
                - {{ $patient->complement }}
            @endif

        </p>


        @if(
            $patient->district
            || $patient->city
            || $patient->state
        )

            <p class="mt-1 text-sm text-slate-500">

                {{ $patient->district }}

                @if(
                    $patient->district
                    && $patient->city
                )
                    -
                @endif

                {{ $patient->city }}

                @if($patient->state)
                    / {{ $patient->state }}
                @endif

            </p>

        @endif

    </div>

</div>