<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $posts = BlogPost::query()
            ->with('creator')
            ->published()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        return view('public.blog.index', ['posts' => $posts, 'search' => $search]);
    }

    public function show(BlogPost $post): View
    {
        // A draft, or an article scheduled for later, stays hidden even when
        // its slug is typed in.
        abort_unless($post->is_published && ($post->published_at === null || $post->published_at->isPast()), 404);

        $post->load('creator');

        return view('public.blog.show', ['post' => $post]);
    }
}
