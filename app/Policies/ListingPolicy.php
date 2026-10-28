<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /** Admin manages every listing; everyone else only the rows they created. */
    public function before(User $user): ?bool
    {
        return $user->role === User::ROLE_ADMIN ? true : null;
    }

    public function update(User $user, Listing $listing): bool
    {
        return $listing->created_by === $user->id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $listing->created_by === $user->id;
    }
}
