<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookPartRequest;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\BookPart;
use Illuminate\Http\RedirectResponse;

class BookPartController extends Controller
{
    public function store(BookPartRequest $request, Book $book): RedirectResponse
    {
        $part = $book->parts()->create($request->validated());

        AuditLog::log(auth()->id(), 'book_part_created', ['book_id' => $book->id, 'part_id' => $part->id]);

        return $this->backToParts($book)->with('success', "{$part->label()} ajoutée.");
    }

    public function update(BookPartRequest $request, BookPart $part): RedirectResponse
    {
        $part->update($request->validated());

        return $this->backToParts($part->book)->with('success', "{$part->label()} mise à jour.");
    }

    /**
     * Supprime la partie et ses pistes ; les abonnements en cours sur cette partie disparaissent avec elle.
     */
    public function destroy(BookPart $part): RedirectResponse
    {
        $label = $part->label();

        $part->tracks->each->delete();
        $part->delete();

        AuditLog::log(auth()->id(), 'book_part_deleted', ['book_id' => $part->book_id, 'title' => $label]);

        return $this->backToParts($part->book)->with('success', "{$label} supprimée.");
    }

    protected function backToParts(Book $book): RedirectResponse
    {
        return redirect()->to(route('admin.books.edit', $book).'#parties-audio');
    }
}
