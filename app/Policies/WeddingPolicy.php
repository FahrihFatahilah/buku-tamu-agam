<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wedding;

class WeddingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Wedding $wedding): bool
    {
        return $user->isSuperAdmin() || $user->client_id === $wedding->client_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isClientAdmin();
    }

    public function update(User $user, Wedding $wedding): bool
    {
        return $user->isSuperAdmin() || ($user->isClientAdmin() && $user->client_id === $wedding->client_id);
    }

    public function delete(User $user, Wedding $wedding): bool
    {
        return $user->isSuperAdmin() || ($user->isClientAdmin() && $user->client_id === $wedding->client_id);
    }

    public function publish(User $user, Wedding $wedding): bool
    {
        return $this->update($user, $wedding);
    }

    public function manageGuests(User $user, Wedding $wedding): bool
    {
        if ($user->isCheckinOperator()) {
            return $user->client_id === $wedding->client_id;
        }
        return $this->update($user, $wedding);
    }

    public function checkIn(User $user, Wedding $wedding): bool
    {
        return $user->isSuperAdmin() || $user->client_id === $wedding->client_id;
    }

    public function manageSettings(User $user, Wedding $wedding): bool
    {
        return $user->isSuperAdmin() || ($user->isClientAdmin() && $user->client_id === $wedding->client_id);
    }
}
