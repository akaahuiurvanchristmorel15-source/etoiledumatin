<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookRequest;
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
    /**
     * Champs fichier du formulaire : [colonne en base, dossier, disque, préfixe public].
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    protected const FILE_FIELDS = [
        'cover_file' => ['cover_image', 'covers', 'public', 'storage/'],
        'pdf_file' => ['pdf_path', 'books', 'local', ''],
        'audio_sample_file' => ['audio_sample_path', 'audio_samples', 'public', 'storage/'],
    ];

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

        if ($request->query('visibility') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('visibility') === 'hidden') {
            $query->where('is_active', false);
        }

        $books = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.books.create', ['book' => new Book, 'categories' => $categories]);
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $data = $this->bookAttributes($request);
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(4));

        $book = Book::create($data);

        AuditLog::log(auth()->id(), 'book_created', ['book_id' => $book->id, 'title' => $book->title]);

        return redirect()->route('admin.books.index')->with('success', "Le livre « {$book->title} » a été ajouté au catalogue.");
    }

    public function edit(Book $book): View
    {
        $categories = Category::orderBy('name')->get();

        $book->load('parts.tracks');

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $book->update($this->bookAttributes($request, $book));

        AuditLog::log(auth()->id(), 'book_updated', ['book_id' => $book->id, 'title' => $book->title]);

        return redirect()->route('admin.books.edit', $book)->with('success', "Le livre « {$book->title} » a été mis à jour.");
    }

    public function destroy(Book $book): RedirectResponse
    {
        $title = $book->title;
        $book->delete();

        AuditLog::log(auth()->id(), 'book_deleted', ['title' => $title]);

        return redirect()->route('admin.books.index')->with('success', "Le livre « {$title} » a été retiré du catalogue.");
    }

    /**
     * Données validées + chemins des fichiers envoyés (les anciens fichiers remplacés sont supprimés).
     *
     * @return array<string, mixed>
     */
    protected function bookAttributes(BookRequest $request, ?Book $book = null): array
    {
        $data = collect($request->validated())->except(array_keys(self::FILE_FIELDS))->all();

        foreach (self::FILE_FIELDS as $field => [$column, $directory, $disk, $publicPrefix]) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $data[$column] = $publicPrefix.$request->file($field)->store($directory, $disk);

            if ($book?->{$column}) {
                Storage::disk($disk)->delete(Str::after($book->{$column}, $publicPrefix));
            }
        }

        return $data;
    }
}
