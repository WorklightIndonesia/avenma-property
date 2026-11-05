<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    /** Every account in the CMS is an admin, so auth is the boundary. */
    public function update(User $user, BlogPost $post): bool
    {
        return true;
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return true;
    }
}
