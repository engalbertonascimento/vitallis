@php
    $user = auth()->user();
@endphp

<header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8">

    {{-- Título da página --}}
    <div>

        <h2 class="font-semibold text-slate-900">
            @yield('header', 'Vitallis')
        </h2>

        @hasSection('subtitle')

            <p class="text-sm text-slate-500">
                @yield('subtitle')
            </p>

        @endif

    </div>


    {{-- Usuário --}}
    <div class="flex items-center gap-3">

        <div class="hidden sm:block text-right">

            <p class="text-sm font-medium text-slate-900">
                {{ $user->name }}
            </p>

            <p class="text-xs text-slate-500">
                {{ $user->email }}
            </p>

        </div>

        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold">

            {{ strtoupper(substr($user->name, 0, 1)) }}

        </div>

    </div>

</header>
