<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogPostRequest;
use App\Models\BlogPost;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['judul']);
        $title = trim((string) ($filters['judul'] ?? ''));
        $sort = (string) $request->query('urut');

        $posts = BlogPost::query()
            ->with('creator')
            ->when($request->user()->role !== User::ROLE_ADMIN, fn ($query) => $query->where('created_by', $request->user()->id))
            ->when($title !== '', fn ($query) => $query->where('title', 'like', "%{$title}%"))
            ->when($sort === 'terlama', fn ($query) => $query->orderBy('published_at')->orderBy('id'), fn ($query) => $query
                ->orderByDesc('published_at')
                ->orderByDesc('id'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog.index', [
            'posts' => $posts,
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }

    public function create(): View
    {
        return view('admin.blog.create', ['post' => new BlogPost]);
    }

    public function store(BlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $post = new BlogPost($data);
        $post->slug = $this->slug($data['title']);
        $post->content = $this->content($data);
        $post->created_by = $request->user()->id;
        $post->published_at = $data['is_published'] ? now() : null;
        $post->save();

        $this->storeCover($post, $request, false);

        return redirect()
            ->route('admin.blog.edit', $post)
            ->with('status', 'Artikel dibuat.');
    }

    public function edit(BlogPost $post): View
    {
        Gate::authorize('update', $post);

        return view('admin.blog.edit', ['post' => $post]);
    }

    public function update(BlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $data = $request->validated();

        $post->fill($data);
        $post->slug = $this->slug($data['title'], $post);
        $post->content = $this->content($data);

        // The first publish sets the date; later edits keep it.
        if ($data['is_published'] && $post->published_at === null) {
            $post->published_at = now();
        }

        $post->save();

        $this->storeCover($post, $request, $data['remove_cover']);

        return redirect()
            ->route('admin.blog.edit', $post)
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        if (filled($post->cover_image)) {
            Storage::disk('uploads')->delete($post->cover_image);
        }

        $post->delete();

        return redirect()
            ->route('admin.blog.index')
            ->with('status', 'Artikel dihapus.');
    }

    /**
     * Quill's empty editor is "<p><br></p>", which the sanitizer reduces to
     * nothing — so a required-but-blank body is caught here, not by validation.
     *
     * @param  array<string, mixed>  $data
     */
    private function content(array $data): string
    {
        $html = HtmlSanitizer::clean($data['content']);

        if ($html === '') {
            throw ValidationException::withMessages(['content' => 'Isi artikel masih kosong.']);
        }

        return $html;
    }

    private function storeCover(BlogPost $post, BlogPostRequest $request, bool $remove): void
    {
        $file = $request->file('cover');

        if ($file === null && ! $remove) {
            return;
        }

        if (filled($post->cover_image)) {
            Storage::disk('uploads')->delete($post->cover_image);
        }

        $post->cover_image = $file?->store('images/blog', 'uploads');
        $post->save();
    }

    /**
     * Regenerated on every save; duplicates get -2, -3, and so on.
     */
    private function slug(string $title, ?BlogPost $post = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $suffix = 2;

        while (BlogPost::query()
            ->where('slug', $slug)
            ->when($post, fn ($query) => $query->whereKeyNot($post->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
