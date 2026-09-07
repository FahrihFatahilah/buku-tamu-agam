@extends('admin.layout')

@section('title', 'Users — ' . $client->name)

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.clients.index') }}" class="hover:text-stone-600">Clients</a>
                <span class="mx-1">›</span> {{ $client->name }}
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Kelola Users</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah User
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-stone-200">
        @if($users->isEmpty())
        <div class="px-6 py-12 text-center text-stone-400 text-sm">Belum ada user untuk client ini.</div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Email</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Role</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($users as $user)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-stone-800">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-stone-500">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @if($user->role === 'client_admin')
                        <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 px-2 py-0.5">Client Admin</span>
                        @else
                        <span class="text-xs bg-stone-100 text-stone-500 px-2 py-0.5">Check-in Operator</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($user->is_active)
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span>Nonaktif
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="openEdit({{ $user->id }}, {{ json_encode($user->only(['name','email','role','is_active'])) }})"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                            <form method="POST" action="{{ route('admin.clients.users.destroy', [$client, $user]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                                    onclick="return confirm('Hapus user {{ addslashes($user->name) }}?')">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Tambah User</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.clients.users.store', $client) }}" class="px-5 py-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                <input type="text" name="name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Email <span class="text-red-400">*</span></label>
                <input type="email" name="email" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Password <span class="text-red-400">*</span></label>
                <input type="password" name="password" required minlength="8" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Role <span class="text-red-400">*</span></label>
                <select name="role" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    <option value="client_admin">Client Admin</option>
                    <option value="checkin_operator">Check-in Operator</option>
                </select>
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
            <h2 class="text-sm font-semibold text-stone-800">Edit User</h2>
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
                <label class="block text-xs text-stone-500 mb-1">Password baru <span class="text-stone-300">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" minlength="8" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Role</label>
                <select name="role" id="edit-role" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    <option value="client_admin">Client Admin</option>
                    <option value="checkin_operator">Check-in Operator</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit-is-active" value="1" class="w-4 h-4 border-stone-300">
                <label for="edit-is-active" class="text-xs text-stone-500">Aktif</label>
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
    document.getElementById('form-edit').action = `/admin/clients/{{ $client->id }}/users/${id}`;
    document.getElementById('edit-name').value = data.name;
    document.getElementById('edit-email').value = data.email;
    document.getElementById('edit-role').value = data.role;
    document.getElementById('edit-is-active').checked = !!data.is_active;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
