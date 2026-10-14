<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with('category');

        if ($request->filled('search')) {
            $s = '%'.trim($request->query('search')).'%';
            $query->where(fn ($q) => $q->where('title', 'like', $s)->orWhere('author', 'like', $s));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        $books = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:50'],
            'pages_count' => ['nullable', 'integer', 'min:1'],
            'published_year' => ['nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 1)],
            'isbn' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'weight_grams' => ['required', 'integer', 'min:50'],
            'price_pdf' => ['required', 'integer', 'min:0'],
            'price_physical' => ['required', 'integer', 'min:0'],
            'price_audio' => ['nullable', 'integer', 'min:0'],
            'stock_physical' => ['required', 'integer', 'min:0'],
            'audio_duration' => ['nullable', 'string', 'max:50'],
            'is_pdf_available' => ['boolean'],
            'is_physical_available' => ['boolean'],
            'is_audio_available' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:102400'],
            'audio_sample_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:20480'],
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(4);
        $validated['price_audio'] = (int) ($validated['price_audio'] ?? 0);
        $validated['is_pdf_available'] = $request->boolean('is_pdf_available');
        $validated['is_physical_available'] = $request->boolean('is_physical_available');
        $validated['is_audio_available'] = $request->boolean('is_audio_available');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);

        // Upload Couverture dans storage public
        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $validated['cover_image'] = 'storage/'.$path;
        }

        // Upload PDF dans storage privé
        if ($request->hasFile('pdf_file')) {
            $path = $request->file('pdf_file')->store('books', 'local');
            $validated['pdf_path'] = $path;
        }

        // Upload Audio complet dans storage privé
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('audiobooks', 'local');
            $validated['audio_path'] = $path;
        }

        // Upload Extrait Audio dans storage public
        if ($request->hasFile('audio_sample_file')) {
            $path = $request->file('audio_sample_file')->store('audio_samples', 'public');
            $validated['audio_sample_path'] = 'storage/'.$path;
        }

        $book = Book::create($validated);

        AuditLog::log(auth()->id(), 'book_created', ['book_id' => $book->id, 'title' => $book->title]);

        return redirect()->route('admin.books.index')->with('success', "Le livre « {$book->title} » a été ajouté au catalogue.");
    }

    public function edit(Book $book): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:50'],
            'pages_count' => ['nullable', 'integer', 'min:1'],
            'published_year' => ['nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 1)],
            'isbn' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'weight_grams' => ['required', 'integer', 'min:50'],
            'price_pdf' => ['required', 'integer', 'min:0'],
            'price_physical' => ['required', 'integer', 'min:0'],
            'price_audio' => ['nullable', 'integer', 'min:0'],
            'stock_physical' => ['required', 'integer', 'min:0'],
            'audio_duration' => ['nullable', 'string', 'max:50'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:102400'],
            'audio_sample_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:20480'],
        ]);

        $validated['price_audio'] = (int) ($validated['price_audio'] ?? 0);
        $validated['is_pdf_available'] = $request->boolean('is_pdf_available');
        $validated['is_physical_available'] = $request->boolean('is_physical_available');
        $validated['is_audio_available'] = $request->boolean('is_audio_available');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $validated['cover_image'] = 'storage/'.$path;
        }

        if ($request->hasFile('pdf_file')) {
            $path = $request->file('pdf_file')->store('books', 'local');
            $validated['pdf_path'] = $path;
        }

        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('audiobooks', 'local');
            $validated['audio_path'] = $path;
        }

        if ($request->hasFile('audio_sample_file')) {
            $path = $request->file('audio_sample_file')->store('audio_samples', 'public');
            $validated['audio_sample_path'] = 'storage/'.$path;
        }

        $book->update($validated);

        AuditLog::log(auth()->id(), 'book_updated', ['book_id' => $book->id, 'title' => $book->title]);

        return redirect()->route('admin.books.index')->with('success', "Le livre « {$book->title} » a été mis à jour.");
    }

    public function destroy(Book $book): RedirectResponse
    {
        $title = $book->title;
        $book->delete();

        AuditLog::log(auth()->id(), 'book_deleted', ['title' => $title]);

        return redirect()->route('admin.books.index')->with('success', "Le livre « {$title} » a été supprimé.");
    }
}
