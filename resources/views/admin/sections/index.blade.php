@extends('admin.layout')

@section('title', 'Sections — ' . $wedding->coupleName())

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Sections
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Sections</h1>
        <p class="text-sm text-stone-400 mt-1">Aktifkan, nonaktifkan, atur background, dan overlay setiap section.</p>
    </div>

    <div class="space-y-2" id="sections-list" x-ref="list"
        x-data="sectionReorder('{{ route('admin.weddings.sections.reorder', $wedding) }}', '{{ csrf_token() }}')">
        @foreach($sections as $section)
        <div class="bg-white border border-stone-200" x-data="{ open: false, draggable: false }" data-id="{{ $section->id }}"
            :draggable="draggable"
            @dragstart="start($event)"
            @dragover="over($event)"
            @drop="drop($event)"
            @dragend="end()">

            {{-- Header row --}}
            <div class="px-4 py-3 flex items-center gap-3">
                <div class="text-stone-300 cursor-grab active:cursor-grabbing select-none text-lg leading-none"
                    title="Geser untuk mengubah urutan"
                    @mousedown="draggable = true"
                    @mouseup="draggable = false"
                    @mouseleave="draggable = false">⠿</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-stone-700">
                        {{ $section->title ?: ucfirst(str_replace('_', ' ', $section->section_key)) }}
                    </p>
                    <p class="text-xs text-stone-400">{{ $section->section_key }}</p>
                </div>

                {{-- Quick toggle --}}
                <form method="POST" action="{{ route('admin.weddings.sections.update', [$wedding, $section]) }}" id="toggle-{{ $section->id }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="sort_order" value="{{ $section->sort_order }}">
                    <input type="hidden" name="title" value="{{ $section->title }}">
                    <input type="hidden" name="is_enabled" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_enabled" value="1" {{ $section->is_enabled ? 'checked' : '' }}
                            class="sr-only peer" onchange="this.form.submit()">
                        <div class="w-9 h-5 bg-stone-200 peer-checked:bg-stone-700 rounded-full transition-colors
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white
                            after:w-4 after:h-4 after:rounded-full after:transition-all peer-checked:after:translate-x-4"></div>
                    </label>
                </form>

                {{-- Expand settings --}}
                <button @click="open = !open"
                    class="text-xs text-stone-400 hover:text-stone-600 transition-colors px-2 py-1 border border-stone-200 hover:border-stone-300">
                    <span x-text="open ? 'Tutup' : 'Atur'"></span>
                </button>

                @if($section->section_key === 'love_story')
                <a href="{{ route('admin.weddings.sections.love-story.index', [$wedding, $section]) }}"
                    class="text-xs text-stone-400 hover:text-stone-600 transition-colors px-2 py-1 border border-stone-200 hover:border-stone-300">
                    Edit Kisah
                </a>
                @endif
            </div>

            {{-- Expanded settings --}}
            <div x-show="open" x-transition class="border-t border-stone-100">
                <form method="POST" action="{{ route('admin.weddings.sections.update', [$wedding, $section]) }}"
                    enctype="multipart/form-data" class="px-4 py-4 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_enabled" value="{{ $section->is_enabled ? '1' : '0' }}">
                    <input type="hidden" name="sort_order" value="{{ $section->sort_order }}">

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Judul Custom</label>
                        <input type="text" name="title" value="{{ $section->title }}" placeholder="Biarkan kosong untuk default"
                            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Background color --}}
                        <div>
                            <label class="block text-xs text-stone-500 mb-1">Warna Background</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="settings[bg_color]"
                                    value="{{ $section->settings['bg_color'] ?? '#ffffff' }}"
                                    class="w-8 h-8 border border-stone-200 cursor-pointer shrink-0">
                                <input type="text" name="settings[bg_color]"
                                    value="{{ $section->settings['bg_color'] ?? '' }}"
                                    placeholder="#ffffff"
                                    class="flex-1 border border-stone-200 px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-stone-400">
                            </div>
                        </div>

                        {{-- Background opacity --}}
                        <div>
                            <label class="block text-xs text-stone-500 mb-1">
                                Opacity Background (<span id="bg-op-val-{{ $section->id }}">{{ $section->settings['bg_opacity'] ?? 100 }}</span>%)
                            </label>
                            <input type="range" name="settings[bg_opacity]" min="0" max="100"
                                value="{{ $section->settings['bg_opacity'] ?? 100 }}"
                                oninput="document.getElementById('bg-op-val-{{ $section->id }}').textContent = this.value"
                                class="w-full">
                        </div>
                    </div>

                    {{-- Background image --}}
                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Gambar Background</label>
                        @if(!empty($section->settings['bg_image']))
                        <div class="mb-2 flex items-center gap-2">
                            <img src="{{ Storage::url($section->settings['bg_image']) }}"
                                class="w-16 h-10 object-cover border border-stone-200">
                            <label class="flex items-center gap-1 text-xs text-stone-400 cursor-pointer">
                                <input type="checkbox" name="settings[remove_bg_image]" value="1" class="w-3 h-3">
                                Hapus
                            </label>
                        </div>
                        @endif
                        <input type="file" name="bg_image_file" accept="image/*"
                            class="text-xs text-stone-600 file:mr-2 file:px-2 file:py-1 file:border file:border-stone-200 file:text-xs file:bg-stone-50 hover:file:bg-stone-100">
                        <div class="mt-1 flex items-center gap-2">
                            <label class="flex items-center gap-1 text-xs text-stone-400 cursor-pointer">
                                <input type="checkbox" name="settings[bg_fixed]" value="1"
                                    {{ !empty($section->settings['bg_fixed']) ? 'checked' : '' }}
                                    class="w-3 h-3">
                                Parallax (fixed)
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-stone-100 pt-4">
                        <p class="text-xs font-medium text-stone-500 mb-3">Overlay Warna</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-stone-400 mb-1">Warna Overlay</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="settings[overlay_color]"
                                        value="{{ $section->settings['overlay_color'] ?? '#000000' }}"
                                        class="w-8 h-8 border border-stone-200 cursor-pointer shrink-0">
                                    <input type="text" name="settings[overlay_color]"
                                        value="{{ $section->settings['overlay_color'] ?? '#000000' }}"
                                        class="flex-1 border border-stone-200 px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-stone-400">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-stone-400 mb-1">
                                    Opacity Overlay (<span id="ov-op-val-{{ $section->id }}">{{ $section->settings['overlay_opacity'] ?? 0 }}</span>%)
                                </label>
                                <input type="range" name="settings[overlay_opacity]" min="0" max="90"
                                    value="{{ $section->settings['overlay_opacity'] ?? 0 }}"
                                    oninput="document.getElementById('ov-op-val-{{ $section->id }}').textContent = this.value"
                                    class="w-full">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-stone-100 pt-4">
                        <p class="text-xs font-medium text-stone-500 mb-3">Overlay Gambar (ornamen, watermark, dll)</p>
                        @if(!empty($section->settings['overlay_image']))
                        <div class="mb-2 flex items-center gap-2">
                            <img src="{{ Storage::url($section->settings['overlay_image']) }}"
                                class="w-16 h-10 object-contain border border-stone-200 bg-stone-50">
                            <label class="flex items-center gap-1 text-xs text-stone-400 cursor-pointer">
                                <input type="checkbox" name="settings[remove_overlay_image]" value="1" class="w-3 h-3">
                                Hapus
                            </label>
                        </div>
                        @endif
                        <input type="file" name="overlay_image_file" accept="image/*"
                            class="text-xs text-stone-600 file:mr-2 file:px-2 file:py-1 file:border file:border-stone-200 file:text-xs file:bg-stone-50 hover:file:bg-stone-100">

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-xs text-stone-400 mb-1">Posisi</label>
                                <select name="settings[overlay_img_position]"
                                    class="w-full border border-stone-200 px-2 py-1.5 text-xs focus:outline-none focus:border-stone-400">
                                    @foreach(['bottom-right'=>'Kanan Bawah','bottom-left'=>'Kiri Bawah','top-right'=>'Kanan Atas','top-left'=>'Kiri Atas','center'=>'Tengah','full'=>'Full Cover'] as $val => $label)
                                    <option value="{{ $val }}" @selected(($section->settings['overlay_img_position'] ?? 'bottom-right') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-stone-400 mb-1">
                                    Opacity (<span id="ov-img-op-{{ $section->id }}">{{ (int)(($section->settings['overlay_img_opacity'] ?? 0.15) * 100) }}</span>%)
                                </label>
                                <input type="range" name="settings[overlay_img_opacity_pct]" min="0" max="100"
                                    value="{{ (int)(($section->settings['overlay_img_opacity'] ?? 0.15) * 100) }}"
                                    oninput="document.getElementById('ov-img-op-{{ $section->id }}').textContent = this.value"
                                    class="w-full">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-xs hover:bg-stone-700 transition-colors">
                            Simpan Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
function sectionReorder(url, token) {
    return {
        url: url,
        token: token,
        dragging: null,

        start(e) {
            this.dragging = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', this.dragging.dataset.id); } catch (_) {}
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
            this.persist();
        },

        end() {
            if (this.dragging) this.dragging.classList.remove('opacity-40');
            this.dragging = null;
            this.persist();
        },

        persist() {
            const order = Array.from(this.$refs.list.children).map(el => el.dataset.id);

            fetch(this.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ order: order }),
            }).catch(() => {});
        }
    };
}
</script>
@endsection
