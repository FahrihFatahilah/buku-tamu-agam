@extends('admin.layout')

@section('title', ($isNew ? 'Template Baru' : $template->name))

@php
    use App\Services\TemplateService;

    $svc    = app(TemplateService::class);
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

    $curPal    = $template->default_settings['palette'] ?? [];
    $curFonts  = $template->default_settings['fonts'] ?? [];
    $curFontD  = old('font_display', $curFonts['display'] ?? 'Playfair Display');
    $curFontB  = old('font_body', $curFonts['body'] ?? 'Lato');

    // Which palette preset matches the stored colours?
    $curPaletteKey = null;
    foreach (config('ngundang.palettes', []) as $pKey => $p) {
        if (($p['primary'] ?? null) === ($curPal['primary'] ?? null) && ($p['accent'] ?? null) === ($curPal['accent'] ?? null)) {
            $curPaletteKey = $pKey;
            break;
        }
    }
    $curPaletteKey = old('palette', $curPaletteKey ?? array_key_first(config('ngundang.palettes', [])));
@endphp

@section('content')
<div class="max-w-5xl" x-data="sectionSorter(@js($rows->pluck('key')->values()))">

    <div class="flex items-start justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.templates.index') }}" class="hover:text-stone-600">Template</a>
                <span class="mx-1">›</span> {{ $isNew ? 'Baru' : $template->name }}
            </p>
            <h1 class="text-xl font-semibold text-stone-800">
                {{ $isNew ? 'Template Baru' : $template->name }}
            </h1>
            <p class="text-sm text-stone-400 mt-1">Geser untuk mengubah urutan, atur judul, dan pilih palet.</p>
        </div>
        @if(!$isNew)
        <a href="{{ route('admin.templates.index') }}" class="text-xs text-stone-400 hover:text-stone-600 transition-colors shrink-0">
            Kembali
        </a>
        @endif
    </div>

    @if($errors->any())
    <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
        action="{{ $isNew ? route('admin.templates.store') : route('admin.templates.update', $template) }}">
        @csrf
        @if(!$isNew) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Section builder ───────────────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-stone-200">
                    <div class="px-5 py-3 border-b border-stone-100 flex items-center justify-between">
                        <h2 class="text-sm font-medium text-stone-700">Urutan Section</h2>
                        <span class="text-xs text-stone-400">{{ $rows->count() }} section</span>
                    </div>

                    <input type="hidden" name="order" :value="JSON.stringify(order)">

                    <div x-ref="list" class="divide-y divide-stone-100">
                        @foreach($rows as $row)
                        @php
                            $key      = $row['key'];
                            $enabled  = (bool) old("enabled.{$key}", $row['enabled']);
                            $title    = old("title.{$key}", $row['title']);
                            $custom   = !$isNew && view()->exists("templates.{$template->key}.sections.{$key}");
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3 bg-white hover:bg-stone-50 transition-colors"
                            draggable="true"
                            data-key="{{ $key }}"
                            @dragstart="start($event)"
                            @dragover="over($event)"
                            @drop="drop($event)"
                            @dragend="end()">

                            <span class="text-stone-300 cursor-grab active:cursor-grabbing select-none text-lg leading-none shrink-0"
                                title="Geser untuk mengubah urutan">⠿</span>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm text-stone-700">{{ $labels[$key] ?? $key }}</p>
                                    <span class="text-xs text-stone-300 font-mono">{{ $key }}</span>
                                    @if($custom)
                                    <span class="text-[10px] uppercase tracking-wide text-stone-400 border border-stone-200 px-1.5 py-px">custom view</span>
                                    @endif
                                </div>
                                <input type="text" name="title[{{ $key }}]" value="{{ $title }}"
                                    placeholder="Judul default (opsional)"
                                    class="mt-1.5 w-full border border-stone-200 px-2 py-1 text-xs focus:outline-none focus:border-stone-400">
                            </div>

                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="hidden" name="enabled[{{ $key }}]" value="0">
                                <input type="checkbox" name="enabled[{{ $key }}]" value="1" {{ $enabled ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-9 h-5 bg-stone-200 peer-checked:bg-stone-700 rounded-full transition-colors
                                    after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white
                                    after:w-4 after:h-4 after:rounded-full after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Template settings ─────────────────────────────────────── --}}
            <div class="space-y-6">
                <div class="bg-white border border-stone-200 p-5 space-y-4">
                    <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Informasi</h2>

                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required value="{{ old('name', $template->name) }}"
                            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    </div>

                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Key</label>
                        <input type="text" name="key" value="{{ old('key', $template->key) }}"
                            placeholder="otomatis dari nama"
                            class="w-full border border-stone-200 px-3 py-2 text-sm font-mono focus:outline-none focus:border-stone-400">
                        <p class="text-xs text-stone-400 mt-1">Huruf kecil, angka, tanda hubung.</p>
                    </div>

                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2"
                            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none">{{ old('description', $template->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-stone-500 mb-1">Kategori</label>
                            <select name="category"
                                class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                                @foreach(['general' => 'Umum', 'cultural' => 'Budaya', 'modern' => 'Modern', 'romantic' => 'Romantis', 'religious' => 'Religius'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('category', $template->category) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-stone-500 mb-1">Urutan</label>
                            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $template->sort_order ?? 0) }}"
                                class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 border-stone-300">
                            Aktif (muncul di pilihan undangan)
                        </label>
                    </div>
                </div>

                {{-- Palette --}}
                <div class="bg-white border border-stone-200 p-5">
                    <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100 mb-4">Palet Warna</h2>
                    <div class="space-y-2">
                        @foreach($palettes as $pKey => $palette)
                        <label class="flex items-center gap-3 px-3 py-2 border cursor-pointer transition-colors
                            {{ $curPaletteKey === $pKey ? 'border-stone-800' : 'border-stone-200 hover:border-stone-300' }}">
                            <input type="radio" name="palette" value="{{ $pKey }}"
                                {{ $curPaletteKey === $pKey ? 'checked' : '' }} class="sr-only peer">
                            <span class="flex -space-x-1 shrink-0">
                                @foreach(['primary', 'secondary', 'accent', 'dark'] as $slot)
                                <span class="w-4 h-4 rounded-full border border-white"
                                    style="background: {{ $palette[$slot] ?? '#e5e5e5' }}"></span>
                                @endforeach
                            </span>
                            <span class="text-xs text-stone-600">{{ $palette['label'] ?? $pKey }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Fonts --}}
                <div class="bg-white border border-stone-200 p-5 space-y-4">
                    <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Tipografi</h2>

                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Font Judul</label>
                        <select name="font_display"
                            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                            @foreach($fonts['display'] as $font)
                            <option value="{{ $font }}" @selected($curFontD === $font)>{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Font Isi</label>
                        <select name="font_body"
                            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                            @foreach($fonts['body'] as $font)
                            <option value="{{ $font }}" @selected($curFontB === $font)>{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2.5 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                    {{ $isNew ? 'Buat Template' : 'Simpan Template' }}
                </button>
            </div>
        </div>
    </form>

    @if(!$isNew)
    <div class="mt-8 pt-6 border-t border-stone-200">
        @if($template->weddings_count ?? $template->weddings()->count())
        <p class="text-xs text-stone-400">
            Template ini dipakai oleh {{ $template->weddings()->count() }} undangan, jadi tidak dapat dihapus.
        </p>
        @else
        <form method="POST" action="{{ route('admin.templates.destroy', $template) }}">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                onclick="return confirm('Hapus template ini?')">Hapus template</button>
        </form>
        @endif
    </div>
    @endif
</div>

<script>
function sectionSorter(initialOrder) {
    return {
        dragging: null,
        order: initialOrder,

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

            const rect  = target.getBoundingClientRect();
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
            this.order = Array.from(this.$refs.list.children).map(el => el.dataset.key);
        }
    };
}
</script>
@endsection
