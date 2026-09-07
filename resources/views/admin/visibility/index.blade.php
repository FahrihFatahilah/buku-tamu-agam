@extends('admin.layout')

@section('title', 'Visibilitas — ' . $wedding->coupleName())

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Visibilitas
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Aturan Visibilitas</h1>
        <p class="text-sm text-stone-400 mt-1">Kontrol konten apa yang terlihat oleh tamu tertentu. Priority: Tamu Individual > Kategori > Default.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Add Rule --}}
        <div class="bg-white border border-stone-200 p-5">
            <h2 class="text-sm font-medium text-stone-700 mb-4">Tambah Aturan</h2>
            <form method="POST" action="{{ route('admin.weddings.visibility.store', $wedding) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Tipe Konten</label>
                    <select name="entity_type" id="entity_type" onchange="updateEntityList()"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="gift_method">Metode Hadiah</option>
                        <option value="event">Acara</option>
                        <option value="section">Section</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Konten</label>
                    <select name="entity_id" id="entity_id"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        @foreach($wedding->giftMethods as $g)
                        <option value="{{ $g->id }}" data-type="gift_method">{{ $g->label }}</option>
                        @endforeach
                        @foreach($wedding->events as $e)
                        <option value="{{ $e->id }}" data-type="event" class="hidden">{{ $e->name }}</option>
                        @endforeach
                        @foreach($wedding->sections as $s)
                        <option value="{{ $s->id }}" data-type="section" class="hidden">{{ $s->section_key }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Berlaku Untuk</label>
                    <select name="scope" id="scope" onchange="updateScopeTarget()"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="wedding_default">Semua Tamu (Default)</option>
                        <option value="category">Kategori Tertentu</option>
                        <option value="guest">Tamu Tertentu</option>
                    </select>
                </div>
                <div id="scope_target_wrap" class="hidden">
                    <label class="block text-xs text-stone-500 mb-1" id="scope_target_label">Pilih Target</label>
                    <select name="scope_id" id="scope_id"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        @foreach($wedding->guestCategories as $cat)
                        <option value="{{ $cat->id }}" data-scope="category">{{ $cat->name }}</option>
                        @endforeach
                        @foreach($wedding->guests()->with('category')->get() as $g)
                        <option value="{{ $g->id }}" data-scope="guest" class="hidden">{{ $g->name }}{{ $g->category ? ' ('.$g->category->name.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Visibilitas</label>
                    <select name="is_visible"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="1">Tampilkan</option>
                        <option value="0">Sembunyikan</option>
                    </select>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                    Simpan Aturan
                </button>
            </form>
        </div>

        {{-- Existing Rules --}}
        <div>
            <h2 class="text-sm font-medium text-stone-700 mb-4">Aturan Aktif</h2>
            @if($wedding->visibilityRules->isEmpty())
            <div class="bg-white border border-stone-200 px-4 py-8 text-center text-stone-400 text-sm">
                Belum ada aturan. Semua konten terlihat oleh semua tamu.
            </div>
            @else
            <div class="space-y-1">
                @foreach($wedding->visibilityRules->groupBy('entity_type') as $type => $rules)
                <div class="bg-white border border-stone-200 p-3">
                    <p class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-2">{{ $type }}</p>
                    @foreach($rules as $rule)
                    <div class="flex items-center justify-between py-1.5 border-b border-stone-50 last:border-0">
                        <div class="text-sm text-stone-700">
                            <span class="font-mono text-xs text-stone-400">ID:{{ $rule->entity_id }}</span>
                            <span class="mx-1 text-stone-300">·</span>
                            <span>{{ $rule->scope }}{{ $rule->scope_id ? ':' . $rule->scope_id : '' }}</span>
                            <span class="mx-1">→</span>
                            <span class="{{ $rule->is_visible ? 'text-green-600' : 'text-red-400' }}">
                                {{ $rule->is_visible ? 'Tampil' : 'Sembunyikan' }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.weddings.visibility.destroy', [$wedding, $rule]) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-stone-300 hover:text-red-400 transition-colors">✕</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function updateEntityList() {
    const type = document.getElementById('entity_type').value;
    document.querySelectorAll('#entity_id option').forEach(o => {
        o.classList.toggle('hidden', o.dataset.type !== type);
    });
    const first = document.querySelector(`#entity_id option[data-type="${type}"]`);
    if (first) first.selected = true;
}

function updateScopeTarget() {
    const scope = document.getElementById('scope').value;
    const wrap = document.getElementById('scope_target_wrap');
    const label = document.getElementById('scope_target_label');
    wrap.classList.toggle('hidden', scope === 'wedding_default');
    label.textContent = scope === 'category' ? 'Pilih Kategori' : 'Pilih Tamu';
    document.querySelectorAll('#scope_id option').forEach(o => {
        o.classList.toggle('hidden', o.dataset.scope !== scope);
    });
    const first = document.querySelector(`#scope_id option[data-scope="${scope}"]`);
    if (first) first.selected = true;
}
</script>
@endsection
