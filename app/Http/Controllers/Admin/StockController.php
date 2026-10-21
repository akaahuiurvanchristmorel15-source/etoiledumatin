<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::where('is_physical_available', true)->with('category');

        if ($request->filled('search')) {
            $search = '%'.trim($request->query('search')).'%';
            $query->where(fn ($q) => $q->where('title', 'like', $search)->orWhere('author', 'like', $search));
        }

        match ($request->query('level')) {
            'out' => $query->where('stock_physical', '<=', 0),
            'low' => $query->whereBetween('stock_physical', [1, Book::LOW_STOCK_THRESHOLD]),
            default => null,
        };

        $books = $query->orderBy('stock_physical')->orderBy('title')->paginate(25)->withQueryString();

        $paperBooks = Book::where('is_physical_available', true);

        return view('admin.stock.index', [
            'books' => $books,
            'totalCopies' => (clone $paperBooks)->sum('stock_physical'),
            'paperTitles' => (clone $paperBooks)->count(),
            'outOfStock' => (clone $paperBooks)->where('stock_physical', '<=', 0)->count(),
            'lowStock' => (clone $paperBooks)->whereBetween('stock_physical', [1, Book::LOW_STOCK_THRESHOLD])->count(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'stock_physical' => ['required', 'integer', 'min:0', 'max:100000'],
        ], [], ['stock_physical' => 'stock']);

        $previous = $book->stock_physical;
        $book->update($validated);

        AuditLog::log($request->user()->id, 'stock_updated', [
            'book_id' => $book->id,
            'title' => $book->title,
            'from' => $previous,
            'to' => $book->stock_physical,
        ]);

        return back()->with('success', "Stock de « {$book->title} » : {$previous} → {$book->stock_physical} exemplaire(s).");
    }
}
