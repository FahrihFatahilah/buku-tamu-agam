<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Builder — {{ $wedding->coupleName() }}</title>
    @vite(['resources/css/builder.css', 'resources/js/builder.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-stone-950 font-sans antialiased overflow-hidden">

<div id="builder-root"
    data-builder="{{ json_encode([
        'document' => $document,
        'registry' => $registry,
        'weddingId' => $wedding->id,
        'urls' => [
            'preview' => $previewUrl,
            'stage' => $stageUrl,
            'save' => $saveUrl,
            'enable' => $enableUrl,
            'revert' => $revertUrl,
            'publish' => $publishUrl,
        ],
    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    class="h-full flex flex-col">

    {{-- ── Toolbar ─────────────────────────────────────────────────────── --}}
    <header class="h-12 shrink-0 flex items-center gap-3 px-3 bg-stone-900 border-b border-white/10 text-stone-300">
        <a href="{{ route('admin.weddings.edit', $wedding) }}"
            class="flex items-center gap-1.5 text-stone-400 hover:text-white transition-colors text-xs shrink-0"
            title="Kembali ke undangan">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        <span class="text-xs font-medium text-stone-200 truncate max-w-[200px]">{{ $wedding->coupleName() }}</span>

        @unless($isActive)
        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-400 shrink-0">
            Page Builder belum aktif
        </span>
        @endunless

        {{-- History --}}
        <div class="flex items-center gap-0.5 shrink-0">
            <button type="button" @click="$store.history.undo()" :disabled="!$store.history.canUndo"
                class="w-7 h-7 flex items-center justify-center rounded hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition-colors"
                title="Undo (Ctrl+Z)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h10a5 5 0 010 10H9M3 10l4-4M3 10l4 4"/>
                </svg>
            </button>
            <button type="button" @click="$store.history.redo()" :disabled="!$store.history.canRedo"
                class="w-7 h-7 flex items-center justify-center rounded hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition-colors"
                title="Redo (Ctrl+Shift+Z)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 10H11a5 5 0 000 10h4m6-10l-4-4m4 4l-4 4"/>
                </svg>
            </button>
        </div>

        {{-- Devices --}}
        <div class="hidden sm:flex items-center gap-0.5 bg-white/5 rounded p-0.5 shrink-0">
            <template x-for="device in [['desktop','Desktop'],['tablet','Tablet'],['mobile','Ponsel']]" :key="device[0]">
                <button type="button" @click="$store.ui.device = device[0]"
                    class="px-2 h-6 text-[11px] rounded transition-colors"
                    :class="$store.ui.device === device[0] ? 'bg-white/15 text-white' : 'text-stone-400 hover:text-white'"
                    x-text="device[1]"></button>
            </template>
        </div>

        <div class="flex-1"></div>

        {{-- Status --}}
        <span class="text-[11px] text-stone-500 shrink-0" x-show="$store.builder.dirty" x-cloak>Belum disimpan</span>
        <span class="text-[11px] text-emerald-500 shrink-0" x-show="!$store.builder.dirty && $store.builder.lastSavedAt" x-cloak>Tersimpan</span>

        {{-- Actions --}}
        <div class="flex items-center gap-2 shrink-0">
            @unless($isActive)
            <button type="button" @click="$store.builder.enable().then(() => location.reload())"
                class="text-[11px] text-stone-300 hover:text-white px-2.5 py-1.5 border border-white/15 rounded transition-colors"
                title="Aktifkan renderer dokumen untuk undangan ini">
                Aktifkan
            </button>
            @endunless

            <button type="button" @click="$store.builder.refreshCanvas()"
                class="text-[11px] text-stone-400 hover:text-white px-2.5 py-1.5 border border-white/15 rounded transition-colors">
                Muat ulang
            </button>

            <a href="{{ $wedding->publicUrl() }}" target="_blank" rel="noopener"
                class="text-[11px] text-stone-400 hover:text-white px-2.5 py-1.5 border border-white/15 rounded transition-colors">
                Pratinjau
            </a>

            <button type="button" @click="$store.builder.save()" :disabled="$store.builder.saving"
                class="text-[11px] bg-white text-stone-900 font-medium px-3.5 py-1.5 rounded hover:bg-stone-200 disabled:opacity-50 transition-colors">
                <span x-show="!$store.builder.saving">Simpan</span>
                <span x-show="$store.builder.saving" x-cloak>Menyimpan…</span>
            </button>
        </div>
    </header>

    {{-- Warnings --}}
    <div x-show="$store.builder.saveErrors.length" x-cloak
        class="shrink-0 px-3 py-1.5 bg-amber-500/10 border-b border-amber-500/20 text-amber-300 text-[11px]">
        <template x-for="error in $store.builder.saveErrors" :key="error">
            <span class="mr-3" x-text="error"></span>
        </template>
    </div>

    @if($usesCodedTemplate)
    <div x-show="!$store.builder.dirty" class="shrink-0 px-3 py-2 bg-blue-500/10 border-b border-blue-500/20 text-blue-200 text-[11px] leading-relaxed">
        Undangan ini masih memakai template <span class="font-mono">{{ $templateKey }}</span> (Blade).
        Selama Page Builder belum diaktifkan dan disimpan, tampilan publiknya tidak berubah.
        <strong class="font-medium">Catatan:</strong> setelah diaktifkan, undangan akan dirender ulang dengan gaya dokumen —
        template <span class="font-mono">{{ $templateKey }}</span> yang ditulis tangan tidak dipakai lagi untuk undangan ini.
    </div>
    @endif

    {{-- ── Body ────────────────────────────────────────────────────────── --}}
    <div class="flex-1 flex min-h-0">

        {{-- Left panel --}}
        <aside x-show="$store.ui.panelsOpen" x-cloak
            class="w-64 shrink-0 bg-stone-900 border-r border-white/10 flex flex-col min-h-0">
            <div class="flex shrink-0 border-b border-white/10">
                <button type="button" @click="$store.ui.leftTab = 'elements'"
                    class="flex-1 py-2 text-[11px] transition-colors"
                    :class="$store.ui.leftTab === 'elements' ? 'text-white border-b-2 border-white' : 'text-stone-500 hover:text-stone-300'">
                    Elemen
                </button>
                <button type="button" @click="$store.ui.leftTab = 'layers'"
                    class="flex-1 py-2 text-[11px] transition-colors"
                    :class="$store.ui.leftTab === 'layers' ? 'text-white border-b-2 border-white' : 'text-stone-500 hover:text-stone-300'">
                    Lapisan
                </button>
            </div>

            <div class="flex-1 min-h-0" x-show="$store.ui.leftTab === 'elements'">
                @include('builder.panels.elements')
            </div>
            <div class="flex-1 min-h-0" x-show="$store.ui.leftTab === 'layers'" x-cloak>
                @include('builder.panels.layers')
            </div>
        </aside>

        {{-- Canvas --}}
        <main class="flex-1 min-w-0 bg-stone-800 overflow-auto">
            <div class="min-h-full flex justify-center p-4">
                <div class="bg-white shadow-2xl overflow-hidden transition-all duration-300 self-start"
                    :style="{ width: $store.ui.frameWidth, maxWidth: '100%' }">
                    <iframe id="builder-canvas"
                        :style="{ height: $store.ui.frameHeight }"
                        title="Kanvas undangan"></iframe>
                </div>
            </div>
        </main>

        {{-- Inspector --}}
        <aside class="w-72 shrink-0 bg-stone-900 border-l border-white/10 min-h-0">
            @include('builder.panels.inspector')
        </aside>
    </div>

    {{-- ── Status bar ──────────────────────────────────────────────────── --}}
    <footer class="h-8 shrink-0 flex items-center gap-3 px-3 bg-stone-900 border-t border-white/10 text-[10px] text-stone-500">
        <span x-text="$store.ui.device"></span>
        <span x-text="(($store.builder.document.nodes || []).length) + ' bagian'"></span>
        <span x-text="(($store.builder.document.overlays || []).length) + ' efek'"></span>
        <div class="flex-1"></div>
        <span>Ctrl+S simpan · Ctrl+Z undo · Ctrl+D duplikat · Del hapus</span>
    </footer>
</div>
</body>
</html>
