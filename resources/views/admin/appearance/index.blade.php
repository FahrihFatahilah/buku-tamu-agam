@extends('admin.layout')

@section('title', 'Tampilan — ' . $wedding->coupleName())

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Tampilan
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Tampilan & Animasi</h1>
    </div>

    {{-- Appearance --}}
    <form method="POST" action="{{ route('admin.weddings.appearance.update', $wedding) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Background Utama</h2>
            <p class="text-xs text-stone-400">Background default untuk semua section yang tidak punya background sendiri.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Warna Background Utama</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="appearance[bg_color]"
                            value="{{ $wedding->appearance['bg_color'] ?? '#f5ede0' }}"
                            class="w-8 h-8 border border-stone-200 cursor-pointer">
                        <input type="text" name="appearance[bg_color]"
                            value="{{ old('appearance.bg_color', $wedding->appearance['bg_color'] ?? '#f5ede0') }}"
                            class="flex-1 border border-stone-200 px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-stone-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Gambar Background Utama</label>
                    @if(!empty($wedding->appearance['bg_image']))
                    <img src="{{ Storage::url($wedding->appearance['bg_image']) }}" class="w-full h-16 object-cover mb-2 border border-stone-200">
                    @endif
                    <input type="file" name="bg_image" accept="image/*"
                        class="text-xs text-stone-600 file:mr-2 file:px-2 file:py-1 file:border file:border-stone-200 file:text-xs file:bg-stone-50 hover:file:bg-stone-100">
                </div>
            </div>
        </div>

        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Warna</h2>
            <p class="text-xs text-stone-400">Override warna default template. Kosongkan untuk menggunakan warna template.</p>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Warna Utama</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="appearance[primary_color]"
                            value="{{ $wedding->appearance['primary_color'] ?? '#7c3238' }}"
                            class="w-8 h-8 border border-stone-200 cursor-pointer">
                        <input type="text" name="appearance[primary_color]"
                            value="{{ old('appearance.primary_color', $wedding->appearance['primary_color'] ?? '#7c3238') }}"
                            class="flex-1 border border-stone-200 px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-stone-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Warna Aksen</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="appearance[accent_color]"
                            value="{{ $wedding->appearance['accent_color'] ?? '#c9a84c' }}"
                            class="w-8 h-8 border border-stone-200 cursor-pointer">
                        <input type="text" name="appearance[accent_color]"
                            value="{{ old('appearance.accent_color', $wedding->appearance['accent_color'] ?? '#c9a84c') }}"
                            class="flex-1 border border-stone-200 px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-stone-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Warna Background</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="appearance[secondary_color]"
                            value="{{ $wedding->appearance['secondary_color'] ?? '#f5ede0' }}"
                            class="w-8 h-8 border border-stone-200 cursor-pointer">
                        <input type="text" name="appearance[secondary_color]"
                            value="{{ old('appearance.secondary_color', $wedding->appearance['secondary_color'] ?? '#f5ede0') }}"
                            class="flex-1 border border-stone-200 px-2 py-1.5 text-xs font-mono focus:outline-none focus:border-stone-400">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Tipografi</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Font Display (judul)</label>
                    <select name="appearance[font_display]"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        @foreach(['Playfair Display','Cormorant Garamond','EB Garamond','Libre Baskerville','Lora'] as $font)
                        <option value="{{ $font }}" @selected(($wedding->appearance['font_display'] ?? 'Playfair Display') === $font)>
                            {{ $font }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Font Body</label>
                    <select name="appearance[font_body]"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        @foreach(['Lato','Inter','DM Sans','Nunito','Open Sans'] as $font)
                        <option value="{{ $font }}" @selected(($wedding->appearance['font_body'] ?? 'Lato') === $font)>
                            {{ $font }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Terminologi</h2>
            <p class="text-xs text-stone-400">Teks yang muncul di undangan. Sesuaikan dengan budaya/agama.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Judul Undangan</label>
                    <input type="text" name="appearance[terminology][wedding_of]"
                        value="{{ old('appearance.terminology.wedding_of', $wedding->appearance['terminology']['wedding_of'] ?? 'Baralek Gadang') }}"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Sub-judul</label>
                    <input type="text" name="appearance[terminology][invitation_title]"
                        value="{{ old('appearance.terminology.invitation_title', $wedding->appearance['terminology']['invitation_title'] ?? "Walimatul 'Ursy") }}"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
        </div>

        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Animasi</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Preset</label>
                    <select name="animation_config[preset]"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        @foreach(['elegant','cinematic','romantic','traditional','minimal','none'] as $preset)
                        <option value="{{ $preset }}" @selected(($wedding->animation_config['preset'] ?? 'elegant') === $preset)>
                            {{ ucfirst($preset) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Kecepatan</label>
                    <select name="animation_config[duration]"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="fast" @selected(($wedding->animation_config['duration'] ?? 'normal') === 'fast')>Cepat</option>
                        <option value="normal" @selected(($wedding->animation_config['duration'] ?? 'normal') === 'normal')>Normal</option>
                        <option value="slow" @selected(($wedding->animation_config['duration'] ?? 'normal') === 'slow')>Lambat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Intensitas</label>
                    <select name="animation_config[intensity]"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="subtle" @selected(($wedding->animation_config['intensity'] ?? 'normal') === 'subtle')>Halus</option>
                        <option value="normal" @selected(($wedding->animation_config['intensity'] ?? 'normal') === 'normal')>Normal</option>
                        <option value="strong" @selected(($wedding->animation_config['intensity'] ?? 'normal') === 'strong')>Kuat</option>
                    </select>
                </div>
            </div>
            <p class="text-xs text-stone-400">Preset "None" menonaktifkan semua animasi. Selalu hormati prefers-reduced-motion.</p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                Simpan Tampilan
            </button>
        </div>
    </form>

    {{-- Settings --}}
    <form method="POST" action="{{ route('admin.weddings.settings.update', $wedding) }}" class="mt-6">
        @csrf
        <div class="bg-white border border-stone-200 p-5 space-y-3">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Pengaturan Undangan</h2>

            @php $settings = $wedding->settings ?? []; @endphp

            <label class="flex items-center justify-between py-1.5">
                <div>
                    <p class="text-sm text-stone-700">Moderasi Buku Tamu</p>
                    <p class="text-xs text-stone-400">Pesan perlu disetujui sebelum tampil publik</p>
                </div>
                <input type="hidden" name="settings[guestbook_moderation]" value="0">
                <input type="checkbox" name="settings[guestbook_moderation]" value="1"
                    {{ ($settings['guestbook_moderation'] ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 border-stone-300">
            </label>

            <label class="flex items-center justify-between py-1.5 border-t border-stone-50">
                <div>
                    <p class="text-sm text-stone-700">RSVP Aktif</p>
                    <p class="text-xs text-stone-400">Tamu dapat mengisi konfirmasi kehadiran</p>
                </div>
                <input type="hidden" name="settings[rsvp_enabled]" value="0">
                <input type="checkbox" name="settings[rsvp_enabled]" value="1"
                    {{ ($settings['rsvp_enabled'] ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 border-stone-300">
            </label>

            <label class="flex items-center justify-between py-1.5 border-t border-stone-50">
                <div>
                    <p class="text-sm text-stone-700">Buku Tamu Aktif</p>
                    <p class="text-xs text-stone-400">Tamu dapat meninggalkan pesan</p>
                </div>
                <input type="hidden" name="settings[guestbook_enabled]" value="0">
                <input type="checkbox" name="settings[guestbook_enabled]" value="1"
                    {{ ($settings['guestbook_enabled'] ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 border-stone-300">
            </label>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
