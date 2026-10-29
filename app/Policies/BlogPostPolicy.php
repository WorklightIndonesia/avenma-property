<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    /** Admin manages every article; everyone else only the rows they wrote. */
    public function before(User $user): ?bool
    {
        return $user->role === User::ROLE_ADMIN ? true : null;
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $post->created_by === $user->id;
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $post->created_by === $user->id;
    }
}
