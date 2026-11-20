<div class="p-6">

    <div class="mb-6">

        <h2 class="text-lg font-semibold text-slate-900">
            Resumo clínico
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Síntese das informações clínicas relevantes do paciente.
        </p>

    </div>


    {{-- =========================================================
         ALERTAS
    ========================================================== --}}
    <section class="mb-6">

        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Alertas
        </h3>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            {{-- Alergias --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">

                <p class="font-semibold text-amber-900">
                    Alergias
                </p>

                <p class="mt-2 text-sm text-amber-700">
                    Nenhuma informação de alergia registrada.
                </p>

            </div>


            {{-- Alertas clínicos --}}
            <div class="rounded-xl border border-red-200 bg-red-50 p-5">

                <p class="font-semibold text-red-900">
                    Alertas clínicos
                </p>

                <p class="mt-2 text-sm text-red-700">
                    Nenhum alerta clínico registrado.
                </p>

            </div>


            {{-- Problemas ativos --}}
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">

                <p class="font-semibold text-blue-900">
                    Problemas ativos
                </p>

                <p class="mt-2 text-sm text-blue-700">
                    Nenhum problema ativo registrado.
                </p>

            </div>

        </div>

    </section>


    {{-- =========================================================
         RESUMO CLÍNICO
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

        {{-- Diagnósticos --}}
        <section class="rounded-xl border border-slate-200 p-5">

            <h3 class="font-semibold text-slate-900">
                Diagnósticos e condições
            </h3>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Nenhum diagnóstico ou condição clínica registrado.
                </p>

            </div>

        </section>


        {{-- Medicamentos --}}
        <section class="rounded-xl border border-slate-200 p-5">

            <h3 class="font-semibold text-slate-900">
                Medicamentos em uso
            </h3>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Nenhum medicamento de uso atual registrado.
                </p>

            </div>

        </section>


        {{-- Evoluções --}}
        <section class="rounded-xl border border-slate-200 p-5">

            <h3 class="font-semibold text-slate-900">
                Últimas evoluções
            </h3>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Nenhuma evolução clínica registrada.
                </p>

            </div>

        </section>


        {{-- Exames --}}
        <section class="rounded-xl border border-slate-200 p-5">

            <h3 class="font-semibold text-slate-900">
                Exames recentes
            </h3>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Nenhum resultado recente disponível.
                </p>

            </div>

        </section>

    </div>

</div>