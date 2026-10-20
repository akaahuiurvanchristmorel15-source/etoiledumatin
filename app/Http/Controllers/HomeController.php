<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Nombre maximum de livres affichés par étagère.
     */
    protected const BOOKS_PER_SHELF = 12;

    public function index(): View
    {
        return view('books.index', [
            'spotlightBooks' => $this->spotlightBooks(),
            'shelves' => $this->shelves(),
            'booksCount' => Book::active()->count(),
        ]);
    }

    /**
     * Livres présentés « à la une » en haut de page : les livres mis en avant, sinon les plus récents.
     *
     * @return Collection<int, Book>
     */
    protected function spotlightBooks(): Collection
    {
        $relations = ['category', 'approvedReviews' => fn ($query) => $query->with('user')->latest()];

        $featured = Book::active()->featured()->with($relations)->latest()->take(5)->get();

        return $featured->isNotEmpty()
            ? $featured
            : Book::active()->with($relations)->latest()->take(5)->get();
    }

    /**
     * Étagères de la page d'accueil : les nouveautés puis une étagère par catégorie active contenant des livres.
     *
     * @return Collection<int, array{title: string, count: int, url: string, books: Collection<int, Book>}>
     */
    protected function shelves(): Collection
    {
        $latestBooks = Book::active()->latest()->take(self::BOOKS_PER_SHELF)->get();

        $categoryShelves = Category::where('is_active', true)
            ->whereHas('books', fn ($query) => $query->active())
            ->withCount(['books' => fn ($query) => $query->active()])
            ->with(['books' => fn ($query) => $query->active()->latest()->limit(self::BOOKS_PER_SHELF)])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'title' => $category->name,
                'count' => $category->books_count,
                'url' => route('catalog.index', ['category' => $category->slug]),
                'books' => $category->books,
            ]);

        return collect([[
            'title' => 'Nouveautés',
            'count' => $latestBooks->count(),
            'url' => route('catalog.index'),
            'books' => $latestBooks,
        ]])
            ->filter(fn (array $shelf) => $shelf['books']->isNotEmpty())
            ->merge($categoryShelves)
            ->values();
    }
}
