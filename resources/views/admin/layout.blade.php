<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Damm Invitation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-stone-50 font-sans antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-full">
    {{-- Sidebar --}}
    <aside class="hidden lg:flex lg:flex-col lg:w-60 lg:fixed lg:inset-y-0 bg-white border-r border-stone-200">
        <div class="flex items-center h-14 px-5 border-b border-stone-200">
            <span class="font-serif text-lg tracking-widest text-stone-800">Dammm invitation</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            @if(auth()->user()->isSuperAdmin())
            <x-nav-item href="{{ route('admin.clients.index') }}" :active="request()->routeIs('admin.clients.*')">
                Clients
            </x-nav-item>
            <x-nav-item href="{{ route('admin.templates.index') }}" :active="request()->routeIs('admin.templates.*')">
                Template
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.index') }}" :active="request()->routeIs('admin.weddings.index')">
                Semua Undangan
            </x-nav-item>
            <x-nav-item href="{{ route('admin.audit.index') }}" :active="request()->routeIs('admin.audit.*')">
                Audit Log
            </x-nav-item>
            @else
            <x-nav-item href="{{ route('admin.weddings.index') }}" :active="request()->routeIs('admin.weddings.index')">
                Undangan Saya
            </x-nav-item>
            @endif

            @if(isset($wedding))
            <div class="pt-3 pb-1">
                <p class="px-2 text-xs font-medium text-stone-400 uppercase tracking-wider truncate">{{ $wedding->coupleName() }}</p>
            </div>
            <x-nav-item href="{{ route('admin.weddings.edit', $wedding) }}" :active="request()->routeIs('admin.weddings.edit')">
                Informasi
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.events.index', $wedding) }}" :active="request()->routeIs('admin.weddings.events.*')">
                Acara
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.sections.index', $wedding) }}" :active="request()->routeIs('admin.weddings.sections.*')">
                Sections
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.media.index', $wedding) }}" :active="request()->routeIs('admin.weddings.media.*')">
                Media
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.playlist.index', $wedding) }}" :active="request()->routeIs('admin.weddings.playlist.*')">
                Musik
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.appearance.index', $wedding) }}" :active="request()->routeIs('admin.weddings.appearance.*')">
                Tampilan
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.guests.index', $wedding) }}" :active="request()->routeIs('admin.weddings.guests.*')">
                Tamu
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.categories.index', $wedding) }}" :active="request()->routeIs('admin.weddings.categories.*')">
                Kategori
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.guestbook.index', $wedding) }}" :active="request()->routeIs('admin.weddings.guestbook.*')">
                Buku Tamu
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.gift.index', $wedding) }}" :active="request()->routeIs('admin.weddings.gift.*')">
                Hadiah
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.visibility.index', $wedding) }}" :active="request()->routeIs('admin.weddings.visibility.*')">
                Visibilitas
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.domains.index', $wedding) }}" :active="request()->routeIs('admin.weddings.domains.*')">
                Domain
            </x-nav-item>
            <x-nav-item href="{{ route('admin.weddings.preview-page', $wedding) }}" :active="request()->routeIs('admin.weddings.preview-page')">
                Preview
            </x-nav-item>
            <x-nav-item href="{{ route('checkin.scanner', $wedding) }}" :active="false">
                Check-in
            </x-nav-item>
            @endif
        </nav>

        <div class="px-3 py-4 border-t border-stone-200">
            <div class="px-2 py-1.5 mb-2">
                <p class="text-xs font-medium text-stone-700">{{ auth()->user()->name }}</p>
                <p class="text-xs text-stone-400">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-stone-500 hover:text-stone-700 transition-colors">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 lg:pl-60 flex flex-col min-h-full">
        {{-- Top bar (mobile) --}}
        <header class="lg:hidden flex items-center h-14 px-4 bg-white border-b border-stone-200">
            <button @click="sidebarOpen = true" class="text-stone-500 hover:text-stone-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="ml-3 font-serif text-base tracking-widest text-stone-800">Ngundang</span>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
            @if(session('success'))
            <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
