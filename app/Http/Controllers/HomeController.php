<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredBooks = Book::active()
            ->featured()
            ->with(['category', 'approvedReviews'])
            ->take(4)
            ->get();

        $latestBooks = Book::active()
            ->with(['category', 'approvedReviews'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::withCount(['books' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('display_order')
            ->get();

        $reviews = Review::where('is_approved', true)
            ->with('book')
            ->latest()
            ->take(6)
            ->get();

        return view('books.index', compact('featuredBooks', 'latestBooks', 'categories', 'reviews'));
    }
}
