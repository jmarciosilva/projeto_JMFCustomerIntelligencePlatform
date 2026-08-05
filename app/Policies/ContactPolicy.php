<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ContactsView->value);
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can(Permission::ContactsView->value);
    }
}
