<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Services\PdfProtectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::active()->with('category');

        // Filtre par catégorie
        if ($categorySlug = $this->queryText($request, 'category')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filtre par auteur
        if ($author = $this->queryText($request, 'author', 255)) {
            $query->where('author', $author);
        }

        // Filtre par langue
        if ($language = $this->queryText($request, 'language', 50)) {
            $query->where('language', $language);
        }

        // Filtre par format
        if ($request->query('format') === 'pdf') {
            $query->where('is_pdf_available', true);
        } elseif ($request->query('format') === 'physical') {
            $query->where('is_physical_available', true);
        } elseif ($request->query('format') === 'audio') {
            $query->where('is_audio_available', true);
        }

        // Filtre par plage de prix : au moins un format réellement en vente doit être dans la fourchette
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $min = (int) $this->queryText($request, 'price_min') ?: 0;
            $max = (int) $this->queryText($request, 'price_max') ?: PHP_INT_MAX;

            $query->where(function ($q) use ($min, $max) {
                foreach (['pdf', 'physical', 'audio'] as $format) {
                    $q->orWhere(fn ($formatQuery) => $formatQuery
                        ->where("is_{$format}_available", true)
                        ->whereBetween("price_{$format}", [$min, $max]));
                }
            });
        }

        // Recherche par mot-clé (titre, auteur, description)
        if ($term = $this->queryText($request, 'search')) {
            $search = '%'.$term.'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('author', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        // Tri
        match ($request->query('sort')) {
            'price_asc' => $query->orderBy('price_pdf', 'asc'),
            'price_desc' => $query->orderBy('price_pdf', 'desc'),
            'author' => $query->orderBy('author', 'asc'),
            default => $query->latest(),
        };

        $books = $query->paginate(12)->withQueryString();

        $categories = Category::withCount(['books' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->get();

        $selectedCategory = $categorySlug
            ? Category::where('slug', $categorySlug)->first()
            : null;

        // Listes distinctes pour les filtres
        $authors = Book::active()->select('author')->distinct()->orderBy('author')->pluck('author');
        $languages = Book::active()->select('language')->distinct()->orderBy('language')->pluck('language');

        return view('pages.catalog', compact('books', 'categories', 'selectedCategory', 'authors', 'languages'));
    }

    public function show(string $slug): View
    {
        $book = Book::active()
            ->where('slug', $slug)
            ->with(['category', 'approvedReviews.user', 'parts.tracks'])
            ->firstOrFail();

        // Livres similaires : même catégorie + même auteur, dédupliqués
        $relatedBooks = Book::active()
            ->where('id', '!=', $book->id)
            ->where(function ($q) use ($book) {
                $q->where('category_id', $book->category_id)
                    ->orWhere('author', $book->author);
            })
            ->with(['category', 'approvedReviews'])
            ->take(4)
            ->get();

        return view('pages.book-detail', compact('book', 'relatedBooks'));
    }

    public function addReview(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'website' => ['prohibited'],
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        Review::create([
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'customer_name' => strip_tags($validated['customer_name']),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => auth()->check() && auth()->user()->isAdmin(),
        ]);

        $message = auth()->check() && auth()->user()->isAdmin()
            ? 'Votre avis a été publié.'
            : 'Merci pour votre avis ! Il sera visible après validation par notre équipe éditoriale.';

        return back()->with('success', $message);
    }

    /**
     * Liseuse de l'extrait gratuit, aux couleurs de la librairie.
     */
    public function preview(string $slug): View
    {
        $book = Book::active()->where('slug', $slug)->where('is_pdf_available', true)->firstOrFail();

        return view('pages.book-preview', compact('book'));
    }

    public function previewPdf(Book $book, PdfProtectionService $pdfProtectionService): StreamedResponse
    {
        abort_unless($book->is_active && $book->is_pdf_available, 404);

        return $pdfProtectionService->streamPreviewPdf($book);
    }
}
