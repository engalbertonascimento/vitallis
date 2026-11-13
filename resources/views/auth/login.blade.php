<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vitallis | Acesso ao sistema</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50">

    <main class="min-h-screen flex">

        {{-- Área institucional --}}
        <section class="hidden lg:flex lg:w-1/2 bg-emerald-700 text-white p-16 flex-col justify-between">

            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center text-2xl font-bold">
                        V
                    </div>

                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight">
                            Vitallis
                        </h1>
                        <p class="text-emerald-100 text-sm">
                            Prontuário Eletrônico
                        </p>
                    </div>
                </div>
            </div>

            <div class="max-w-lg">
                <h2 class="text-4xl font-semibold leading-tight">
                    Informação clínica segura para um cuidado mais conectado.
                </h2>

                <p class="mt-6 text-lg text-emerald-100 leading-relaxed">
                    Gestão integrada de pacientes, atendimentos e informações
                    clínicas entre unidades autorizadas.
                </p>
            </div>

            <div class="text-sm text-emerald-100">
                © {{ date('Y') }} Vitallis
            </div>

        </section>

        {{-- Login --}}
        <section class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">

            <div class="w-full max-w-md">

                {{-- Marca no mobile --}}
                <div class="lg:hidden text-center mb-10">
                    <h1 class="text-3xl font-semibold text-emerald-700">
                        Vitallis
                    </h1>
                    <p class="text-slate-500">
                        Prontuário Eletrônico
                    </p>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-semibold text-slate-900">
                        Bem-vindo
                    </h2>

                    <p class="mt-2 text-slate-500">
                        Entre com suas credenciais para acessar o sistema.
                    </p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">

                    @csrf

                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-slate-700 mb-2"
                        >
                            E-mail
                        </label>
                    
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            placeholder="seuemail@empresa.com.br"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >
                    
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">

                            <label
                                for="password"
                                class="text-sm font-medium text-slate-700"
                            >
                                Senha
                            </label>

                            <a
                                href="#"
                                class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                            >
                                Esqueceu sua senha?
                            </a>

                        </div>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="Digite sua senha"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                        >
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600"
                        >

                        Manter conectado
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-emerald-700 px-4 py-3 font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                    >
                        Entrar
                    </button>

                </form>

                <div class="mt-10 border-t border-slate-200 pt-6 text-center">

                    <p class="text-xs text-slate-400">
                        Acesso restrito a usuários autorizados.
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        As operações realizadas no sistema poderão ser registradas para fins de segurança e auditoria.
                    </p>

                </div>

            </div>

        </section>

    </main>

</body>
</html>
