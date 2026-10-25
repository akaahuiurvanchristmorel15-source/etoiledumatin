<?php

namespace App\Http\Controllers;

use App\Enums\NewsType;
use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Nombre d'actualités par page.
     */
    protected const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $selectedType = NewsType::tryFrom((string) $request->query('type'));

        $news = News::published()
            ->with('book')
            ->when($selectedType, fn ($query) => $query->where('type', $selectedType))
            ->latest('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.news.index', [
            'news' => $news,
            'selectedType' => $selectedType,
            'upcomingEvents' => News::published()
                ->whereNotNull('event_date')
                ->where('event_date', '>=', now())
                ->orderBy('event_date')
                ->take(3)
                ->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $article = News::published()->with('book')->where('slug', $slug)->firstOrFail();

        $related = News::published()
            ->with('book')
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('pages.news.show', compact('article', 'related'));
    }
}
