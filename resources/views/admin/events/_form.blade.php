@php $prefix = isset($edit) ? 'edit-' : ''; @endphp

<div class="grid grid-cols-2 gap-3">
    <div class="col-span-2">
        <label class="block text-xs text-stone-500 mb-1">Nama Acara <span class="text-red-400">*</span></label>
        <input type="text" name="name" id="{{ $prefix }}name" required
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div>
        <label class="block text-xs text-stone-500 mb-1">Tipe</label>
        <select name="type" id="{{ $prefix }}type" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            <option value="akad">Akad Nikah</option>
            <option value="reception">Resepsi</option>
            <option value="pengajian">Pengajian</option>
            <option value="siraman">Siraman</option>
            <option value="custom">Lainnya</option>
        </select>
    </div>
    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
            <input type="checkbox" name="is_public" id="{{ $prefix }}is_public" value="1" checked
                class="w-4 h-4 border-stone-300">
            Tampilkan ke publik
        </label>
    </div>
    <div>
        <label class="block text-xs text-stone-500 mb-1">Mulai</label>
        <input type="datetime-local" name="starts_at" id="{{ $prefix }}starts_at"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div>
        <label class="block text-xs text-stone-500 mb-1">Selesai</label>
        <input type="datetime-local" name="ends_at" id="{{ $prefix }}ends_at"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div class="col-span-2">
        <label class="block text-xs text-stone-500 mb-1">Nama Venue</label>
        <input type="text" name="venue" id="{{ $prefix }}venue"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div class="col-span-2">
        <label class="block text-xs text-stone-500 mb-1">Alamat</label>
        <textarea name="address" id="{{ $prefix }}address" rows="2"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
    </div>
    <div class="col-span-2">
        <label class="block text-xs text-stone-500 mb-1">Google Maps URL</label>
        <input type="url" name="maps_url" id="{{ $prefix }}maps_url"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div>
        <label class="block text-xs text-stone-500 mb-1">Dress Code</label>
        <input type="text" name="dress_code" id="{{ $prefix }}dress_code"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div>
        <label class="block text-xs text-stone-500 mb-1">Catatan</label>
        <input type="text" name="notes" id="{{ $prefix }}notes"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
</div>
