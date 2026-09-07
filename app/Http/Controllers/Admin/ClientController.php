<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::withCount(['weddings', 'users'])
            ->latest()->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:clients,email',
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:100',
        ]);

        $client = Client::create(array_merge($validated, ['status' => 'active']));

        // Optionally create client admin user
        if ($request->filled('admin_email')) {
            $request->validate([
                'admin_email'    => 'required|email|unique:users,email',
                'admin_name'     => 'required|string|max:100',
                'admin_password' => 'required|string|min:8',
            ]);

            User::create([
                'name'      => $request->admin_name,
                'email'     => $request->admin_email,
                'password'  => Hash::make($request->admin_password),
                'client_id' => $client->id,
                'role'      => 'client_admin',
                'is_active' => true,
            ]);
        }

        return back()->with('success', "Client {$client->name} berhasil dibuat.");
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:clients,email,' . $client->id,
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:100',
            'status'  => 'required|in:active,suspended',
        ]);

        $client->update($validated);

        return back()->with('success', 'Client berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return back()->with('success', 'Client berhasil dihapus.');
    }
}
