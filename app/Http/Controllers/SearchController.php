<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Résultats instantanés de la fenêtre de recherche : livres publiés dont le titre ou l'auteur correspond.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:80']]);
        $term = '%'.trim($validated['q']).'%';

        $books = Book::active()
            ->where(fn ($query) => $query->where('title', 'like', $term)->orWhere('author', 'like', $term))
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$term])
            ->latest()
            ->take(6)
            ->get(['id', 'title', 'slug', 'author', 'cover_image', 'is_pdf_available', 'is_physical_available', 'is_audio_available']);

        return response()->json([
            'books' => $books->map(fn (Book $book) => [
                'title' => $book->title,
                'author' => $book->author,
                'url' => route('catalog.show', $book->slug),
                'cover' => $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg'),
                'formats' => collect(['PDF' => $book->is_pdf_available, 'Papier' => $book->is_physical_available, 'Audio' => $book->is_audio_available])->filter()->keys()->implode(' · '),
            ])->values(),
            'catalogUrl' => route('catalog.index', ['search' => trim($validated['q'])]),
        ]);
    }
}
