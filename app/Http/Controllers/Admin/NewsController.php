<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NewsType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $query = News::query();

        if ($search = $this->queryText($request, 'search')) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($type = NewsType::tryFrom($this->queryText($request, 'type'))) {
            $query->where('type', $type);
        }

        if ($request->query('status') === 'published') {
            $query->where('is_published', true);
        } elseif ($request->query('status') === 'draft') {
            $query->where('is_published', false);
        }

        $news = $query->latest()->paginate(15)->withQueryString();

        return view('admin.news.index', compact('news'));
    }

    public function create(): View
    {
        return view('admin.news.create', [
            'article' => new News(['type' => NewsType::Event]),
            'books' => $this->books(),
        ]);
    }

    public function store(NewsRequest $request): RedirectResponse
    {
        $article = News::create([
            ...$this->newsAttributes($request),
            'slug' => $this->uniqueSlug($request->validated('title')),
        ]);

        AuditLog::log(auth()->id(), 'news_created', ['news_id' => $article->id, 'title' => $article->title]);

        return redirect()->route('admin.news.index')->with('success', "L'actualité « {$article->title} » a été créée.");
    }

    public function edit(News $news): View
    {
        return view('admin.news.edit', ['article' => $news, 'books' => $this->books()]);
    }

    public function update(NewsRequest $request, News $news): RedirectResponse
    {
        $data = $this->newsAttributes($request, $news);

        if ($data['title'] !== $news->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $news);
        }

        $news->update($data);

        AuditLog::log(auth()->id(), 'news_updated', ['news_id' => $news->id, 'title' => $news->title]);

        return redirect()->route('admin.news.edit', $news)->with('success', "L'actualité « {$news->title} » a été mise à jour.");
    }

    public function destroy(News $news): RedirectResponse
    {
        $title = $news->title;

        if ($news->cover_image) {
            Storage::disk('public')->delete(Str::after($news->cover_image, 'storage/'));
        }

        $news->delete();

        AuditLog::log(auth()->id(), 'news_deleted', ['title' => $title]);

        return redirect()->route('admin.news.index')->with('success', "L'actualité « {$title} » a été supprimée.");
    }

    /**
     * Données validées + couverture envoyée (l'ancienne image remplacée ou retirée est supprimée).
     * Une actualité publiée sans date de publication est datée de maintenant.
     *
     * @return array<string, mixed>
     */
    protected function newsAttributes(NewsRequest $request, ?News $news = null): array
    {
        $data = collect($request->validated())->except(['cover_file', 'remove_cover'])->all();

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = $news?->published_at ?? now();
        }

        if ($request->hasFile('cover_file') || $request->boolean('remove_cover')) {
            if ($news?->cover_image) {
                Storage::disk('public')->delete(Str::after($news->cover_image, 'storage/'));
            }

            $data['cover_image'] = $request->hasFile('cover_file')
                ? 'storage/'.$request->file('cover_file')->store('news', 'public')
                : null;
        }

        return $data;
    }

    /**
     * @return Collection<int, Book>
     */
    protected function books(): Collection
    {
        return Book::orderBy('title')->get(['id', 'title', 'author']);
    }

    protected function uniqueSlug(string $title, ?News $ignore = null): string
    {
        $baseSlug = Str::slug($title) ?: 'actualite';
        $slug = $baseSlug;
        $suffix = 2;

        while (News::where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
