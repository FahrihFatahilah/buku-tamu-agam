@extends('admin.layout')

@section('title', 'Clients')

@section('content')
<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-stone-800">Clients</h1>
            <p class="text-sm text-stone-400 mt-0.5">{{ $clients->total() }} client terdaftar</p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah Client
        </button>
    </div>

    <div class="bg-white border border-stone-200">
        @if($clients->isEmpty())
        <div class="px-6 py-12 text-center text-stone-400 text-sm">Belum ada client.</div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Client</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide hidden md:table-cell">Email</th>
                    <th class="text-center px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Undangan</th>
                    <th class="text-center px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Users</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($clients as $client)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-stone-800">{{ $client->name }}</p>
                        @if($client->company)<p class="text-xs text-stone-400">{{ $client->company }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-stone-500 hidden md:table-cell">{{ $client->email }}</td>
                    <td class="px-4 py-3 text-center text-stone-600">{{ $client->weddings_count }}</td>
                    <td class="px-4 py-3 text-center text-stone-600">{{ $client->users_count }}</td>
                    <td class="px-4 py-3">
                        @if($client->status === 'active')
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs text-amber-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Suspended
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="openEdit({{ $client->id }}, {{ json_encode($client->only(['name','email','phone','company','status'])) }})"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                                    onclick="return confirm('Hapus client {{ addslashes($client->name) }}? Semua data terkait akan dihapus.')">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($clients->hasPages())
        <div class="px-4 py-3 border-t border-stone-100">{{ $clients->links() }}</div>
        @endif
        @endif
    </div>
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 sticky top-0 bg-white">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Client</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.clients.store') }}" class="px-5 py-4 space-y-3">
            @csrf
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wide">Informasi Client</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-stone-500 mb-1">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">No. HP</label>
                    <input type="text" name="phone" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Perusahaan</label>
                    <input type="text" name="company" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div class="pt-2 border-t border-stone-100">
                <p class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-3">Admin Client (opsional)</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Nama Admin</label>
                        <input type="text" name="admin_name" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    </div>
                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Email Admin</label>
                        <input type="email" name="admin_email" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    </div>
                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Password</label>
                        <input type="password" name="admin_password" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    </div>
                </div>
            </div>
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
    <div class="bg-white w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Edit Client</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs text-stone-500 mb-1">Nama</label>
                <input type="text" name="name" id="edit-name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Email</label>
                <input type="email" name="email" id="edit-email" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Status</label>
                <select name="status" id="edit-status" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    <option value="active">Aktif</option>
                    <option value="suspended">Suspended</option>
                </select>
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
function openEdit(id, data) {
    document.getElementById('form-edit').action = `/admin/clients/${id}`;
    document.getElementById('edit-name').value = data.name;
    document.getElementById('edit-email').value = data.email;
    document.getElementById('edit-status').value = data.status;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
