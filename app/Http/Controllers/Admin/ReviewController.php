<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['book', 'user']);

        if ($request->query('filter') === 'pending') {
            $query->where('is_approved', false);
        } elseif ($request->query('filter') === 'approved') {
            $query->where('is_approved', true);
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);

        AuditLog::log(auth()->id(), 'review_approved', ['review_id' => $review->id]);

        return back()->with('success', 'L\'avis a été approuvé et est désormais visible sur la fiche du livre.');
    }

    public function hide(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => false]);

        AuditLog::log(auth()->id(), 'review_hidden', ['review_id' => $review->id]);

        return back()->with('success', 'L\'avis a été masqué de la fiche du livre.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        AuditLog::log(auth()->id(), 'review_deleted', ['review_id' => $review->id]);

        return back()->with('success', 'L\'avis a été supprimé.');
    }
}
