@extends('admin.layout')

@section('title', 'Kisah Cinta — ' . $wedding->coupleName())

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.sections.index', $wedding) }}" class="hover:text-stone-600">Sections</a>
            <span class="mx-1">›</span> Kisah Cinta
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Kisah Cinta</h1>
        <p class="text-xs text-stone-400 mt-1">Cerita perjalanan cinta yang ditampilkan di undangan.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Tambah kisah --}}
    <div class="bg-white border border-stone-200 p-5 mb-6">
        <h2 class="text-sm font-medium text-stone-700 mb-4">+ Tambah Kisah</h2>
        <form method="POST" action="{{ route('admin.weddings.sections.love-story.store', [$wedding, $section]) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Tahun / Tanggal <span class="text-red-400">*</span></label>
                    <input type="text" name="year" placeholder="cth: 2024 December" required
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Judul <span class="text-red-400">*</span></label>
                    <input type="text" name="title" placeholder="cth: Awal Pertemuan" required
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Cerita</label>
                <textarea name="description" rows="3" placeholder="Ceritakan momen ini..."
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Tambah</button>
            </div>
        </form>
    </div>

    {{-- Daftar kisah --}}
    @if(empty($stories))
    <div class="bg-white border border-stone-200 px-6 py-10 text-center text-stone-400 text-sm">
        Belum ada kisah. Tambahkan kisah pertama di atas.
    </div>
    @else
    <div class="space-y-3">
        @foreach($stories as $i => $story)
        <div class="bg-white border border-stone-200 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-amber-600 font-medium bg-amber-50 border border-amber-200 px-2 py-0.5">{{ $story['year'] }}</span>
                        <span class="font-medium text-stone-800 text-sm">{{ $story['title'] }}</span>
                    </div>
                    @if(!empty($story['description']))
                    <p class="text-xs text-stone-500 leading-relaxed line-clamp-2">{{ $story['description'] }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button onclick="openEdit({{ $i }}, {{ json_encode($story) }})"
                        class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                    <form method="POST" action="{{ route('admin.weddings.sections.love-story.destroy', [$wedding, $section, $i]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                            onclick="return confirm('Hapus kisah ini?')">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Modal Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Edit Kisah</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Tahun / Tanggal</label>
                    <input type="text" name="year" id="edit-year" required
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Judul</label>
                    <input type="text" name="title" id="edit-title" required
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Cerita</label>
                <textarea name="description" id="edit-description" rows="4"
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(index, data) {
    const base = '{{ url("admin/weddings/{$wedding->id}/sections/{$section->id}/love-story") }}';
    document.getElementById('form-edit').action = base + '/' + index;
    document.getElementById('edit-year').value = data.year || '';
    document.getElementById('edit-title').value = data.title || '';
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
