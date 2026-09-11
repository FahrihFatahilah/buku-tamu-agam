@extends('admin.templates.builder-layout')

@section('title', $isNew ? 'Template Baru' : $template->name)

@php
    $svc    = app(App\Services\TemplateService::class);
    $labels = $svc->sectionLabels();

    // Restore the drag order on a validation round-trip, otherwise use the stored layout.
    $layoutKeys = collect($layout)->pluck('key')->all();
    if (old('order')) {
        $decoded = json_decode(old('order'), true) ?: [];
        $valid   = array_values(array_intersect($decoded, $layoutKeys));
        $layoutKeys = array_merge($valid, array_values(array_diff($layoutKeys, $valid)));
    }

    $rows = collect($layoutKeys)->map(function ($key) use ($layout) {
        return collect($layout)->firstWhere('key', $key)
            ?? ['key' => $key, 'enabled' => true, 'title' => null];
    });

    $enabledMap = $rows->mapWithKeys(fn($r) => [$r['key'] => (bool) old("enabled.{$r['key']}", $r['enabled'])]);
    $titleMap   = $rows->mapWithKeys(fn($r) => [$r['key'] => (string) old("title.{$r['key']}", $r['title'] ?? '')]);

    $curPal   = $template->default_settings['palette'] ?? [];
    $curFonts = $template->default_settings['fonts'] ?? [];
    $curFontD = old('font_display', $curFonts['display'] ?? 'Playfair Display');
    $curFontB = old('font_body', $curFonts['body'] ?? 'Lato');

    $curPaletteKey = null;
    foreach ($palettes as $pKey => $p) {
        if (($p['primary'] ?? null) === ($curPal['primary'] ?? null) && ($p['accent'] ?? null) === ($curPal['accent'] ?? null)) {
            $curPaletteKey = $pKey;
            break;
        }
    }
    $curPaletteKey = old('palette', $curPaletteKey ?? array_key_first($palettes));
@endphp

@section('content')
<form method="POST"
    action="{{ $isNew ? route('admin.templates.store') : route('admin.templates.update', $template) }}"
    x-data="templateBuilder(@js([
        'order'       => array_values($layoutKeys),
        'enabled'     => $enabledMap,
        'titles'      => $titleMap,
        'palette'     => $curPaletteKey,
        'fontDisplay' => $curFontD,
        'fontBody'    => $curFontB,
        'previewBase' => route('admin.templates.preview'),
        'templateId'  => $isNew ? null : $template->id,
    ]))"
    class="h-full flex flex-col">
    @csrf
    @if(!$isNew) @method('PUT') @endif

    <input type="hidden" name="order" :value="JSON.stringify(order)">

    {{-- ── Top bar ──────────────────────────────────────────────────────── --}}
    <header class="h-14 shrink-0 flex items-center justify-between gap-4 px-4 bg-stone-900 border-b border-white/10 text-white">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('admin.templates.index') }}"
                class="flex items-center gap-1.5 text-stone-400 hover:text-white transition-colors text-sm shrink-0"
                title="Kembali ke daftar template">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span class="text-sm font-medium truncate">{{ $isNew ? 'Template Baru' : $template->name }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full shrink-0"
                :class="enabledCount === order.length ? 'bg-green-500/15 text-green-400' : 'bg-amber-500/15 text-amber-400'">
                <span x-text="enabledCount"></span>/<span x-text="order.length"></span> section
            </span>
        </div>

        {{-- Device switcher --}}
        <div class="hidden sm:flex items-center gap-1 bg-white/5 rounded p-0.5">
            <template x-for="d in [['desktop','Monitor'],['tablet','Tablet'],['mobile','Ponsel']]" :key="d[0]">
                <button type="button" @click="device = d[0]"
                    class="w-8 h-7 flex items-center justify-center rounded transition-colors"
                    :class="device === d[0] ? 'bg-white/15 text-white' : 'text-stone-400 hover:text-white'"
                    :title="d[1]">
                    <svg x-show="d[0] === 'desktop'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <svg x-show="d[0] === 'tablet'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <svg x-show="d[0] === 'mobile'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </button>
            </template>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" @click="panels = !panels"
                class="text-xs text-stone-400 hover:text-white transition-colors px-2.5 py-1.5 border border-white/15 rounded"
                x-text="panels ? 'Fokus' : 'Panel'"></button>
            <button type="button" @click="reload()"
                class="text-xs text-stone-400 hover:text-white transition-colors px-2.5 py-1.5 border border-white/15 rounded">
                Muat ulang
            </button>
            <button type="submit"
                class="text-xs bg-white text-stone-900 font-medium px-3.5 py-1.5 rounded hover:bg-stone-200 transition-colors">
                {{ $isNew ? 'Buat' : 'Simpan' }}
            </button>
        </div>
    </header>

    @if($errors->any())
    <div class="shrink-0 px-4 py-2 bg-red-500/15 border-b border-red-500/30 text-red-300 text-xs">
        @foreach($errors->all() as $error)
        <span class="mr-3">{{ $error }}</span>
        @endforeach
    </div>
    @endif

    <div class="flex-1 flex min-h-0">

        {{-- ── Left panel ───────────────────────────────────────────────── --}}
        <aside x-show="panels" x-cloak
            class="w-72 shrink-0 bg-stone-900 border-r border-white/10 flex flex-col min-h-0 text-stone-300">

            {{-- Tabs --}}
            <div class="flex shrink-0 border-b border-white/10">
                <button type="button" @click="tab = 'structure'"
                    class="flex-1 py-2.5 text-xs font-medium transition-colors"
                    :class="tab === 'structure' ? 'text-white border-b-2 border-white' : 'text-stone-500 hover:text-stone-300'">
                    Struktur
                </button>
                <button type="button" @click="tab = 'settings'"
                    class="flex-1 py-2.5 text-xs font-medium transition-colors"
                    :class="tab === 'settings' ? 'text-white border-b-2 border-white' : 'text-stone-500 hover:text-stone-300'">
                    Pengaturan
                </button>
            </div>

            {{-- Structure tab --}}
            <div x-show="tab === 'structure'" class="flex-1 overflow-y-auto min-h-0">
                <p class="px-3 py-2 text-[11px] text-stone-500 border-b border-white/5">
                    Geser untuk mengubah urutan. Klik untuk melihat di kanvas.
                </p>

                <div x-ref="list" class="py-1">
                    @foreach($rows as $row)
                    @php
                        $key    = $row['key'];
                        $custom = !$isNew && view()->exists("templates.{$template->key}.sections.{$key}");
                    @endphp
                    <div data-key="{{ $key }}"
                        draggable="true"
                        @dragstart="start($event)"
                        @dragover="over($event)"
                        @drop="drop($event)"
                        @dragend="end()"
                        @click="select('{{ $key }}')"
                        class="group px-3 py-2 cursor-pointer border-l-2 transition-colors"
                        :class="selected === '{{ $key }}'
                            ? 'border-white bg-white/5'
                            : 'border-transparent hover:bg-white/5'">

                        <div class="flex items-center gap-2">
                            <span class="text-stone-600 cursor-grab active:cursor-grabbing select-none text-sm leading-none shrink-0">⠿</span>
                            <span class="flex-1 text-sm truncate"
                                :class="enabled['{{ $key }}'] ? 'text-stone-200' : 'text-stone-500 line-through'">
                                {{ $labels[$key] ?? $key }}
                            </span>
                            @if($custom)
                            <span class="text-[9px] uppercase tracking-wide text-stone-500 border border-white/15 px-1 rounded shrink-0">custom</span>
                            @endif
                            <button type="button" @click.stop="toggle('{{ $key }}')"
                                class="shrink-0 w-6 h-6 flex items-center justify-center rounded hover:bg-white/10 transition-colors"
                                :title="enabled['{{ $key }}'] ? 'Sembunyikan' : 'Tampilkan'">
                                <svg x-show="enabled['{{ $key }}']" class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="!enabled['{{ $key }}']" class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Inline title editor for the selected section --}}
                        <div x-show="selected === '{{ $key }}'" x-cloak class="mt-2 ml-5">
                            <input type="text"
                                name="title[{{ $key }}]"
                                :value="titles['{{ $key }}']"
                                @input="titles['{{ $key }}'] = $event.target.value"
                                @click.stop
                                placeholder="Judul custom (opsional)"
                                class="w-full bg-white/5 border border-white/10 px-2 py-1 text-xs text-stone-200 placeholder-stone-600 rounded focus:outline-none focus:border-white/30">
                            <p class="text-[10px] text-stone-600 mt-1 font-mono">{{ $key }}</p>
                        </div>

                        {{-- Submitted values (must stay in the DOM) --}}
                        <input type="hidden" name="enabled[{{ $key }}]" value="0">
                        <input type="checkbox" name="enabled[{{ $key }}]" value="1" class="hidden"
                            :checked="enabled['{{ $key }}']">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Settings tab --}}
            <div x-show="tab === 'settings'" x-cloak class="flex-1 overflow-y-auto min-h-0 p-3 space-y-4">
                <div>
                    <label class="block text-[11px] text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $template->name) }}"
                        class="w-full bg-white/5 border border-white/10 px-2.5 py-2 text-sm text-stone-100 rounded focus:outline-none focus:border-white/30">
                </div>

                <div>
                    <label class="block text-[11px] text-stone-500 mb-1">Key</label>
                    <input type="text" name="key" value="{{ old('key', $template->key) }}" placeholder="otomatis dari nama"
                        class="w-full bg-white/5 border border-white/10 px-2.5 py-2 text-xs font-mono text-stone-100 rounded focus:outline-none focus:border-white/30">
                </div>

                <div>
                    <label class="block text-[11px] text-stone-500 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2"
                        class="w-full bg-white/5 border border-white/10 px-2.5 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30 resize-none">{{ old('description', $template->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] text-stone-500 mb-1">Kategori</label>
                        <select name="category"
                            class="w-full bg-white/5 border border-white/10 px-2 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30">
                            @foreach(['general' => 'Umum', 'cultural' => 'Budaya', 'modern' => 'Modern', 'romantic' => 'Romantis', 'religious' => 'Religius'] as $val => $label)
                            <option value="{{ $val }}" class="bg-stone-800" @selected(old('category', $template->category) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-stone-500 mb-1">Urutan</label>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $template->sort_order ?? 0) }}"
                            class="w-full bg-white/5 border border-white/10 px-2 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-xs text-stone-300 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                        class="w-3.5 h-3.5 rounded border-white/20 bg-white/5">
                    Aktif (muncul di pilihan undangan)
                </label>

                {{-- Palette --}}
                <div class="pt-3 border-t border-white/10">
                    <p class="text-[11px] text-stone-500 mb-2">Palet Warna</p>
                    <div class="space-y-1.5">
                        @foreach($palettes as $pKey => $palette)
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded cursor-pointer transition-colors"
                            :class="palette === '{{ $pKey }}' ? 'bg-white/10 ring-1 ring-white/30' : 'hover:bg-white/5'">
                            <input type="radio" name="palette" value="{{ $pKey }}" class="hidden"
                                :checked="palette === '{{ $pKey }}'"
                                @change="palette = '{{ $pKey }}'; schedule()">
                            <span class="flex -space-x-1 shrink-0">
                                @foreach(['primary', 'secondary', 'accent', 'dark'] as $slot)
                                <span class="w-3.5 h-3.5 rounded-full border border-white/20"
                                    style="background: {{ $palette[$slot] ?? '#e5e5e5' }}"></span>
                                @endforeach
                            </span>
                            <span class="text-[11px] text-stone-300">{{ $palette['label'] ?? $pKey }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Fonts --}}
                <div class="pt-3 border-t border-white/10 space-y-3">
                    <p class="text-[11px] text-stone-500">Tipografi</p>
                    <div>
                        <label class="block text-[11px] text-stone-500 mb-1">Font Judul</label>
                        <select name="font_display" :value="fontDisplay"
                            @change="fontDisplay = $event.target.value; schedule()"
                            class="w-full bg-white/5 border border-white/10 px-2 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30">
                            @foreach($fonts['display'] as $font)
                            <option value="{{ $font }}" class="bg-stone-800">{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-stone-500 mb-1">Font Isi</label>
                        <select name="font_body" :value="fontBody"
                            @change="fontBody = $event.target.value; schedule()"
                            class="w-full bg-white/5 border border-white/10 px-2 py-2 text-xs text-stone-100 rounded focus:outline-none focus:border-white/30">
                            @foreach($fonts['body'] as $font)
                            <option value="{{ $font }}" class="bg-stone-800">{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(!$isNew)
                <div class="pt-3 border-t border-white/10">
                    <p class="text-[11px] text-stone-500">
                        Dipakai {{ $template->weddings()->count() }} undangan.
                        Perubahan berlaku untuk undangan baru.
                    </p>
                </div>
                @endif
            </div>
        </aside>

        {{-- ── Canvas ───────────────────────────────────────────────────── --}}
        <main class="flex-1 min-w-0 bg-stone-800 overflow-auto">
            <div class="min-h-full flex justify-center p-4 sm:p-8 transition-all">
                <div class="bg-white shadow-2xl overflow-hidden transition-all duration-300 self-start"
                    :style="frameStyle">
                    <iframe x-ref="frame"
                        :src="previewSrc"
                        @load="onFrameLoad()"
                        class="w-full block border-0"
                        :style="{ height: frameHeight }"
                        title="Pratinjau template"></iframe>
                </div>
            </div>
        </main>
    </div>

    @if(!$isNew)
    <div class="shrink-0 px-4 py-2 bg-stone-900 border-t border-white/10 flex items-center justify-between">
        <p class="text-[11px] text-stone-500">Pratinjau memakai konten contoh.</p>
        @if($template->weddings()->count())
        <p class="text-[11px] text-stone-600">Template ini dipakai {{ $template->weddings()->count() }} undangan, jadi tidak dapat dihapus.</p>
        @else
        <button type="submit" form="template-delete" class="text-[11px] text-red-400 hover:text-red-300 transition-colors"
            onclick="return confirm('Hapus template ini?')">Hapus template</button>
        @endif
    </div>
    @endif
</form>

@if(!$isNew && !$template->weddings()->count())
<form id="template-delete" method="POST" action="{{ route('admin.templates.destroy', $template) }}">
    @csrf @method('DELETE')
</form>
@endif

<script>
function templateBuilder(config) {
    return {
        order: config.order,
        enabled: config.enabled,
        titles: config.titles,
        palette: config.palette,
        fontDisplay: config.fontDisplay,
        fontBody: config.fontBody,
        previewBase: config.previewBase,
        templateId: config.templateId,

        tab: 'structure',
        panels: true,
        device: 'desktop',
        selected: config.order[0] ?? null,
        previewSrc: '',
        dragging: null,
        timer: null,

        init() {
            this.refresh(0);
        },

        get enabledCount() {
            return this.order.filter(k => this.enabled[k]).length;
        },

        get frameStyle() {
            const widths = { desktop: '100%', tablet: '768px', mobile: '390px' };
            return { width: this.panels ? widths[this.device] : widths[this.device], maxWidth: '100%' };
        },

        get frameHeight() {
            return this.device === 'mobile' ? '760px' : this.device === 'tablet' ? '900px' : '1000px';
        },

        previewUrl() {
            const p = new URLSearchParams();
            p.set('palette', this.palette);
            p.set('font_display', this.fontDisplay);
            p.set('font_body', this.fontBody);
            p.set('order', this.order.join(','));
            p.set('off', this.order.filter(k => !this.enabled[k]).join(','));
            if (this.templateId) p.set('template', this.templateId);
            return this.previewBase + '?' + p.toString();
        },

        schedule() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.reload(), 350);
        },

        reload() {
            this.previewSrc = this.previewUrl();
        },

        onFrameLoad() {
            // Re-apply the selected section highlight after every reload.
            if (this.selected) {
                const el = this.$refs.frame?.contentDocument?.getElementById(this.selected);
                if (el) el.scrollIntoView({ block: 'start' });
            }
        },

        select(key) {
            this.selected = key;
            const el = this.$refs.frame?.contentDocument?.getElementById(key);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        toggle(key) {
            this.enabled[key] = !this.enabled[key];
            this.schedule();
        },

        start(e) {
            this.dragging = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', this.dragging.dataset.key); } catch (_) {}
            this.dragging.classList.add('opacity-40');
        },

        over(e) {
            e.preventDefault();
            const target = e.currentTarget;
            if (!this.dragging || target === this.dragging) return;
            if (target.parentElement !== this.$refs.list) return;

            const rect = target.getBoundingClientRect();
            const after = (e.clientY - rect.top) > rect.height / 2;
            this.$refs.list.insertBefore(this.dragging, after ? target.nextSibling : target);
        },

        drop(e) {
            e.preventDefault();
            this.sync();
        },

        end() {
            if (this.dragging) this.dragging.classList.remove('opacity-40');
            this.dragging = null;
            this.sync();
        },

        sync() {
            // Assign a fresh array so the preview URL recomputes.
            this.order = Array.from(this.$refs.list.children).map(el => el.dataset.key);
            this.schedule();
        }
    };
}
</script>
@endsection
