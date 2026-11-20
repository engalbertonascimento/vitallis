<div class="p-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h2 class="text-lg font-semibold text-slate-900">
                {{ $tabLabel }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Conteúdo clínico do paciente.
            </p>

        </div>


        @if($canEditClinicalRecord)

            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                Edição habilitada
            </span>

        @else

            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">
                Somente leitura
            </span>

        @endif

    </div>


    <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">

        <p class="font-medium text-slate-700">
            {{ $tabLabel }}
        </p>

        <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-slate-500">
            Esta ficha já faz parte da estrutura do prontuário.
            Os registros clínicos específicos serão implementados
            gradualmente sem alterar a navegação do paciente.
        </p>

    </div>

</div>