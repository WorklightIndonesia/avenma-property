<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /** Every account in the CMS is an admin, so auth is the boundary. */
    public function update(User $user, Listing $listing): bool
    {
        return true;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return true;
    }
}
