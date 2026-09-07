<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->isSuperAdmin() || $user->client_id === $client->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isSuperAdmin();
    }
}
