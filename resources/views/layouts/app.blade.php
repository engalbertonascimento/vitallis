<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Vitallis')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="bg-slate-50 text-slate-900">

<div class="min-h-screen flex">

    {{-- Sidebar global --}}
    @include('partials.sidebar')


    {{-- Área principal --}}
    <main class="flex-1 min-w-0">

        {{-- Header global --}}
        @include('partials.header')


        {{-- Mensagem de sucesso --}}
        @if(session('success'))

            <div class="mx-6 lg:mx-8 mt-6">

                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">

                    {{ session('success') }}

                </div>

            </div>

        @endif


        {{-- Conteúdo específico --}}
        <div class="p-6 lg:p-8">

            @yield('content')

        </div>

    </main>

</div>

</body>

</html>