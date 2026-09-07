@extends('admin.layout')

@section('title', 'Hadiah — ' . $wedding->coupleName())

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
                <span class="mx-1">›</span> Hadiah
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Metode Hadiah</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah
        </button>
    </div>

    @if($gifts->isEmpty())
    <div class="bg-white border border-stone-200 px-6 py-12 text-center text-stone-400 text-sm">
        Belum ada metode hadiah.
    </div>
    @else
    <div class="space-y-2">
        @foreach($gifts as $gift)
        <div class="bg-white border border-stone-200 px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-medium text-stone-800">{{ $gift->label }}</p>
                    <span class="text-xs text-stone-400 bg-stone-100 px-2 py-0.5">{{ $gift->type }}</span>
                    @if(!$gift->is_active)
                    <span class="text-xs text-stone-300">Nonaktif</span>
                    @endif
                </div>
                @if($gift->bank_name)
                <p class="text-sm text-stone-500 mt-0.5">{{ $gift->bank_name }} — {{ $gift->account_number }}</p>
                <p class="text-xs text-stone-400">a.n. {{ $gift->account_holder }}</p>
                @endif
                @if($gift->merchant_name)
                <p class="text-sm text-stone-500 mt-0.5">{{ $gift->merchant_name }}</p>
                @endif
                @if($gift->description)
                <p class="text-xs text-stone-400 mt-0.5">{{ $gift->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="openEdit({{ $gift->id }}, {{ json_encode($gift->toArray()) }})"
                    class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                <form method="POST" action="{{ route('admin.weddings.gift.destroy', [$wedding, $gift]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                        onclick="return confirm('Hapus metode hadiah ini?')">Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 sticky top-0 bg-white">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Metode Hadiah</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.weddings.gift.store', $wedding) }}"
            enctype="multipart/form-data" class="px-5 py-4 space-y-3">
            @csrf
            @include('admin.gift._form')
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
    <div class="bg-white w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 sticky top-0 bg-white">
            <h2 class="text-sm font-semibold text-stone-800">Edit Metode Hadiah</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" enctype="multipart/form-data" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            @include('admin.gift._form', ['edit' => true])
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
    const base = '{{ url("admin/weddings/" . $wedding->id . "/gift") }}';
    document.getElementById('form-edit').action = base + '/' + id;
    ['type','label','bank_name','account_number','account_holder','merchant_name','description'].forEach(f => {
        const el = document.getElementById('edit-' + f);
        if (el) el.value = data[f] || '';
    });
    const active = document.getElementById('edit-is_active');
    if (active) active.checked = !!data.is_active;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
