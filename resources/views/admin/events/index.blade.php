@extends('admin.layout')

@section('title', 'Acara — ' . $wedding->coupleName())

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
                <span class="mx-1">›</span> Acara
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Acara</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah Acara
        </button>
    </div>

    @if($events->isEmpty())
    <div class="bg-white border border-stone-200 px-6 py-12 text-center text-stone-400 text-sm">
        Belum ada acara. Tambahkan akad nikah, resepsi, dll.
    </div>
    @else
    <div class="space-y-2">
        @foreach($events as $event)
        <div class="bg-white border border-stone-200 px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-medium text-stone-800">{{ $event->name }}</p>
                    <span class="text-xs text-stone-400 bg-stone-100 px-2 py-0.5">{{ $event->type }}</span>
                    @if(!$event->is_public)
                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5">Private</span>
                    @endif
                </div>
                @if($event->starts_at)
                <p class="text-sm text-stone-500 mt-1">{{ $event->starts_at->format('d M Y, H:i') }}
                    @if($event->ends_at) — {{ $event->ends_at->format('H:i') }} @endif
                </p>
                @endif
                @if($event->venue)
                <p class="text-sm text-stone-400 mt-0.5">{{ $event->venue }}</p>
                @endif
                @if($event->dress_code)
                <p class="text-xs text-stone-400 mt-0.5">Dress code: {{ $event->dress_code }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="openEdit({{ $event->id }}, {{ json_encode($event->toArray()) }})"
                    class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                <form method="POST" action="{{ route('admin.weddings.events.destroy', [$wedding, $event]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                        onclick="return confirm('Hapus acara ini?')">Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 sticky top-0 bg-white">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Acara</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.weddings.events.store', $wedding) }}" class="px-5 py-4 space-y-3">
            @csrf
            @include('admin.events._form')
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 sticky top-0 bg-white">
            <h2 class="text-sm font-semibold text-stone-800">Edit Acara</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            @include('admin.events._form', ['edit' => true])
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, data) {
    const base = '{{ url("admin/weddings/" . $wedding->id . "/events") }}';
    document.getElementById('form-edit').action = base + '/' + id;
    ['name','type','venue','address','maps_url','dress_code','notes'].forEach(f => {
        const el = document.getElementById('edit-' + f);
        if (el) el.value = data[f] || '';
    });
    const sa = document.getElementById('edit-starts_at');
    const ea = document.getElementById('edit-ends_at');
    if (sa) sa.value = data.starts_at ? data.starts_at.substring(0,16) : '';
    if (ea) ea.value = data.ends_at ? data.ends_at.substring(0,16) : '';
    const pub = document.getElementById('edit-is_public');
    if (pub) pub.checked = data.is_public;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
