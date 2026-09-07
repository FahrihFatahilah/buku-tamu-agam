@php $prefix = isset($edit) ? 'edit-' : ''; @endphp

<div>
    <label class="block text-xs text-stone-500 mb-1">Tipe <span class="text-red-400">*</span></label>
    <select name="type" id="{{ $prefix }}type" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
        <option value="bank_transfer">Transfer Bank</option>
        <option value="qris">QRIS</option>
        <option value="e_wallet">E-Wallet</option>
        <option value="cash">Cash</option>
        <option value="custom">Lainnya</option>
    </select>
</div>
<div>
    <label class="block text-xs text-stone-500 mb-1">Label <span class="text-red-400">*</span></label>
    <input type="text" name="label" id="{{ $prefix }}label" required placeholder="cth: BCA, GoPay, QRIS"
        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
</div>
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs text-stone-500 mb-1">Nama Bank</label>
        <input type="text" name="bank_name" id="{{ $prefix }}bank_name"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
    <div>
        <label class="block text-xs text-stone-500 mb-1">No. Rekening</label>
        <input type="text" name="account_number" id="{{ $prefix }}account_number"
            class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
    </div>
</div>
<div>
    <label class="block text-xs text-stone-500 mb-1">Atas Nama</label>
    <input type="text" name="account_holder" id="{{ $prefix }}account_holder"
        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
</div>
<div>
    <label class="block text-xs text-stone-500 mb-1">Nama Merchant (QRIS)</label>
    <input type="text" name="merchant_name" id="{{ $prefix }}merchant_name"
        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
</div>
<div>
    <label class="block text-xs text-stone-500 mb-1">Gambar QRIS</label>
    <input type="file" name="image" accept="image/*"
        class="text-sm text-stone-600 file:mr-2 file:px-3 file:py-1.5 file:border file:border-stone-200 file:text-xs file:bg-stone-50 file:text-stone-600 hover:file:bg-stone-100">
</div>
<div>
    <label class="block text-xs text-stone-500 mb-1">Deskripsi</label>
    <input type="text" name="description" id="{{ $prefix }}description"
        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
</div>
<div>
    <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
        <input type="checkbox" name="is_active" id="{{ $prefix }}is_active" value="1" checked
            class="w-4 h-4 border-stone-300">
        Aktif
    </label>
</div>
