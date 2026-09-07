@extends('admin.layout')

@section('title', $wedding->coupleName())

@section('content')
{{-- Header --}}
<div class="mb-6 flex items-start justify-between">
    <div>
        <a href="{{ route('admin.weddings.index') }}" class="text-sm text-stone-400 hover:text-stone-600 transition-colors">← Undangan</a>
        <h1 class="text-xl font-medium text-stone-800 mt-1">{{ $wedding->coupleName() }}</h1>
        <div class="flex items-center gap-3 mt-1">
            <span class="font-mono text-xs text-stone-400">{{ $wedding->public_id }}</span>
            @if($wedding->isPublished())
                <span class="inline-flex items-center gap-1.5 text-xs text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif
                </span>
                <a href="{{ $wedding->publicUrl() }}" target="_blank" class="text-xs text-stone-400 hover:text-stone-600 transition-colors">
                    Lihat ↗
                </a>
                @if($wedding->short_id)
                <span class="font-mono text-xs text-stone-300">{{ $wedding->shortUrl() }}</span>
                @endif
            @elseif($wedding->status === 'archived')
                <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span>Arsip
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span>Draft
                </span>
                <a href="{{ route('admin.weddings.preview', $wedding) }}" class="text-xs text-stone-400 hover:text-stone-600 transition-colors">
                    Preview ↗
                </a>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2">
        @if($wedding->isPublished())
        <form method="POST" action="{{ route('admin.weddings.unpublish', $wedding) }}">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm border border-stone-300 text-stone-600 hover:border-stone-400 transition-colors">
                Unpublish
            </button>
        </form>
        @elseif($wedding->status === 'draft')
        <form method="POST" action="{{ route('admin.weddings.publish', $wedding) }}">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm bg-stone-800 text-white hover:bg-stone-700 transition-colors">
                Publish
            </button>
        </form>
        @endif

        @if($wedding->status !== 'archived')
        <form method="POST" action="{{ route('admin.weddings.archive', $wedding) }}">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm text-stone-400 hover:text-stone-600 transition-colors"
                onclick="return confirm('Arsipkan undangan ini?')">
                Arsipkan
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Dashboard Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
    <div class="bg-white border border-stone-200 px-4 py-3">
        <p class="text-2xl font-semibold text-stone-800">{{ $stats['guests'] }}</p>
        <p class="text-xs text-stone-400 mt-0.5">Total Tamu</p>
    </div>
    <div class="bg-white border border-stone-200 px-4 py-3">
        <p class="text-2xl font-semibold text-green-600">{{ $stats['rsvp_yes'] }}</p>
        <p class="text-xs text-stone-400 mt-0.5">RSVP Hadir</p>
    </div>
    <div class="bg-white border border-stone-200 px-4 py-3">
        <p class="text-2xl font-semibold text-blue-600">{{ $stats['checkins'] }}</p>
        <p class="text-xs text-stone-400 mt-0.5">Check-in</p>
    </div>
    <div class="bg-white border border-stone-200 px-4 py-3">
        <p class="text-2xl font-semibold text-amber-600">{{ $stats['pending_messages'] }}</p>
        <p class="text-xs text-stone-400 mt-0.5">Pesan Pending</p>
    </div>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.weddings.update', $wedding) }}"
        enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Template --}}
        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <h2 class="text-sm font-medium text-stone-700">Template</h2>
                <a href="{{ route('admin.weddings.preview', $wedding) }}" target="_blank"
                    class="text-xs text-stone-400 hover:text-stone-600 transition-colors">
                    Preview undangan ↗
                </a>
            </div>
            @php
            $templatePalettes = [
                'minang-elegance'     => ['bg' => '#2C1810', 'accent' => '#B8960C', 'text' => '#F5F0E8', 'label' => 'Maroon · Cream · Gold'],
                'modern-luxury'       => ['bg' => '#0a0a0a', 'accent' => '#c9a84c', 'text' => '#ffffff', 'label' => 'Black · White · Gold'],
                'floral-romantic'     => ['bg' => '#f9e8e8', 'accent' => '#c97b84', 'text' => '#4a2030', 'label' => 'Rose · Blush · Sage'],
                'islamic-elegant'     => ['bg' => '#1a3a2a', 'accent' => '#c9a84c', 'text' => '#f5f0e8', 'label' => 'Green · Cream · Gold'],
                'traditional-nusantara' => ['bg' => '#3d2010', 'accent' => '#c9a84c', 'text' => '#f5ede0', 'label' => 'Brown · Cream · Gold'],
                'minimalist'          => ['bg' => '#ffffff', 'accent' => '#1a1a1a', 'text' => '#1a1a1a', 'label' => 'Black · White · Grey'],
            ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($templates as $tpl)
                @php $palette = $templatePalettes[$tpl->key] ?? ['bg'=>'#f5f5f5','accent'=>'#333','text'=>'#333','label'=>'']; @endphp
                <label class="relative cursor-pointer group">
                    <input type="radio" name="template_id" value="{{ $tpl->id }}"
                        {{ $wedding->template_id == $tpl->id ? 'checked' : '' }}
                        class="sr-only peer">
                    <div class="border-2 border-stone-200 peer-checked:border-stone-800 transition-colors overflow-hidden">
                        {{-- Mini preview --}}
                        <div class="h-24 relative flex flex-col items-center justify-center px-2"
                            style="background:{{ $palette['bg'] }};">
                            {{-- Ornamen garis --}}
                            <div class="absolute top-2 left-2 w-4 h-4 border-t border-l opacity-40" style="border-color:{{ $palette['accent'] }}"></div>
                            <div class="absolute top-2 right-2 w-4 h-4 border-t border-r opacity-40" style="border-color:{{ $palette['accent'] }}"></div>
                            <div class="absolute bottom-2 left-2 w-4 h-4 border-b border-l opacity-40" style="border-color:{{ $palette['accent'] }}"></div>
                            <div class="absolute bottom-2 right-2 w-4 h-4 border-b border-r opacity-40" style="border-color:{{ $palette['accent'] }}"></div>
                            {{-- Nama pengantin mini --}}
                            <p class="text-center leading-tight text-xs opacity-80" style="color:{{ $palette['text'] }};font-family:Georgia,serif;">{{ $wedding->bride_name }}</p>
                            <p class="text-xs my-0.5" style="color:{{ $palette['accent'] }}">&</p>
                            <p class="text-center leading-tight text-xs opacity-80" style="color:{{ $palette['text'] }};font-family:Georgia,serif;">{{ $wedding->groom_name }}</p>
                            {{-- Checked indicator --}}
                            <div class="absolute top-1.5 right-1.5 w-4 h-4 bg-stone-800 rounded-full hidden peer-checked:flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                        {{-- Info --}}
                        <div class="p-2 bg-white">
                            <p class="text-xs font-medium text-stone-700">{{ $tpl->name }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">{{ $palette['label'] }}</p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            <p class="text-xs text-stone-400">Mengganti template tidak menghapus data undangan.</p>
        </div>

        {{-- Couple --}}
        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Informasi Pengantin</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Nama Pengantin Pria</label>
                    <input type="text" name="groom_name" value="{{ old('groom_name', $wedding->groom_name) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Nama Pengantin Wanita</label>
                    <input type="text" name="bride_name" value="{{ old('bride_name', $wedding->bride_name) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Panggilan Pria</label>
                    <input type="text" name="groom_nickname" value="{{ old('groom_nickname', $wedding->groom_nickname) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Panggilan Wanita</label>
                    <input type="text" name="bride_nickname" value="{{ old('bride_nickname', $wedding->bride_nickname) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Ayah Pengantin Wanita</label>
                    <input type="text" name="bride_father" value="{{ old('bride_father', $wedding->bride_father) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Ibu Pengantin Wanita</label>
                    <input type="text" name="bride_mother" value="{{ old('bride_mother', $wedding->bride_mother) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Ayah Pengantin Pria</label>
                    <input type="text" name="groom_father" value="{{ old('groom_father', $wedding->groom_father) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Ibu Pengantin Pria</label>
                    <input type="text" name="groom_mother" value="{{ old('groom_mother', $wedding->groom_mother) }}"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Deskripsi / Pembuka</label>
                <textarea name="description" rows="3"
                    class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors resize-none">{{ old('description', $wedding->description) }}</textarea>
            </div>

            {{-- Ayat Al-Quran --}}
            <div class="border-t border-stone-100 pt-4 space-y-3">
                <p class="text-xs font-medium text-stone-500 uppercase tracking-wide">Ayat Al-Quran (Section Quote)</p>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Teks Arab</label>
                    <textarea name="settings[quote_arabic]" rows="3" dir="rtl"
                        placeholder="وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم ..."
                        style="font-family: 'Scheherazade New', 'Amiri', serif; font-size: 1.3rem; line-height: 2;"
                        class="w-full px-3 py-2 border border-stone-300 text-right focus:outline-none focus:border-stone-500 transition-colors resize-none">{{ old('settings.quote_arabic', $wedding->settings['quote_arabic'] ?? '') }}</textarea>
                    <p class="text-xs text-stone-400 mt-1">Tulis atau paste teks Arab. Font akan otomatis disesuaikan di undangan.</p>
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Terjemahan</label>
                    <textarea name="quote" rows="3"
                        placeholder="Dan di antara tanda-tanda kekuasaan-Nya ..."
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors resize-none">{{ old('quote', (!$wedding->quote || preg_match('/^(Q\.?S\.?|Surah|QS)/i', trim($wedding->quote ?? ''))) ? '' : $wedding->quote) }}</textarea>
                    <p class="text-xs text-stone-400 mt-1">Isi dengan terjemahan ayat, bukan nama surah.</p>
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Sumber Ayat</label>
                    <input type="text" name="settings[quote_source]"
                        value="{{ old('settings.quote_source', $wedding->settings['quote_source'] ?? 'QS. Ar-Rum: 21') }}"
                        placeholder="QS. Ar-Rum: 21"
                        class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
                </div>
            </div>
        </div>

        {{-- Event --}}
        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Tanggal & Lokasi</h2>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Tanggal Pernikahan</label>
                <input type="date" name="date" value="{{ old('date', $wedding->date?->format('Y-m-d')) }}"
                    class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Nama Venue</label>
                <input type="text" name="venue" value="{{ old('venue', $wedding->venue) }}"
                    class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Alamat</label>
                <textarea name="address" rows="2"
                    class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors resize-none">{{ old('address', $wedding->address) }}</textarea>
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Slug URL</label>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-stone-400">{{ $wedding->public_id }}/</span>
                    <input type="text" name="slug" value="{{ old('slug', $wedding->slug) }}"
                        class="flex-1 px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors font-mono">
                </div>
                <p class="text-xs text-stone-400 mt-1">Slug lama otomatis redirect ke slug baru.</p>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">SEO & Social</h2>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">SEO Title</label>
                <input type="text" name="seo_title" value="{{ old('seo_title', $wedding->seo_title) }}"
                    class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1.5">SEO Description</label>
                <textarea name="seo_description" rows="2"
                    class="w-full px-3 py-2 border border-stone-300 text-sm focus:outline-none focus:border-stone-500 transition-colors resize-none">{{ old('seo_description', $wedding->seo_description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">OG Image (WhatsApp preview)</label>
                    @if($wedding->og_image)
                    <img src="{{ Storage::url($wedding->og_image) }}" class="w-full h-24 object-cover mb-2 border border-stone-200">
                    @endif
                    <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                        class="text-xs text-stone-600 file:mr-2 file:px-2 file:py-1 file:border file:border-stone-200 file:text-xs file:bg-stone-50 hover:file:bg-stone-100">
                    <p class="text-xs text-stone-400 mt-1">JPG/PNG/WebP, maks 2MB. Ideal: 1200×630px.</p>
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Favicon</label>
                    @if($wedding->favicon)
                    <img src="{{ Storage::url($wedding->favicon) }}" class="w-8 h-8 mb-2 border border-stone-200">
                    @endif
                    <input type="file" name="favicon" accept="image/x-icon,image/png"
                        class="text-xs text-stone-600 file:mr-2 file:px-2 file:py-1 file:border file:border-stone-200 file:text-xs file:bg-stone-50 hover:file:bg-stone-100">
                    <p class="text-xs text-stone-400 mt-1">ICO/PNG, maks 512KB.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="px-5 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
