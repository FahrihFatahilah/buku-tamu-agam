<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

/**
 * Templates are platform-wide assets, so only Super Admins manage them
 * (spec §4). Client admins may select a template for their wedding but
 * not create, edit, or delete templates.
 */
class TemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Template $template): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Template $template): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Template $template): bool
    {
        return $user->isSuperAdmin() && ! $template->weddings()->exists();
    }
}
