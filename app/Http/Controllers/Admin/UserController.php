<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Client $client)
    {
        $this->authorize('viewAny', Client::class);

        $users = $client->users()->latest()->get();

        return view('admin.users.index', compact('client', 'users'));
    }

    public function store(Request $request, Client $client)
    {
        $this->authorize('viewAny', Client::class);

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:client_admin,checkin_operator',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'client_id' => $client->id,
            'role'      => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', "User {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, Client $client, User $user)
    {
        $this->authorize('viewAny', Client::class);
        abort_if($user->client_id !== $client->id, 403);

        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'role'      => 'required|in:client_admin,checkin_operator',
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8',
        ]);

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Client $client, User $user)
    {
        $this->authorize('viewAny', Client::class);
        abort_if($user->client_id !== $client->id, 403);

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
