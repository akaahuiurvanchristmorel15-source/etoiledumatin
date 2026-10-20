<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount(['books' => fn ($query) => $query->withTrashed()])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true]),
            'parents' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create([
            ...$request->validated(),
            'slug' => $this->uniqueSlug($request->validated('name')),
        ]);

        AuditLog::log(auth()->id(), 'category_created', ['category_id' => $category->id, 'name' => $category->name]);

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été créée.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => Category::whereKeyNot($category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category);
        }

        $category->update($data);

        AuditLog::log(auth()->id(), 'category_updated', ['category_id' => $category->id, 'name' => $category->name]);

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$category->name} » a été mise à jour.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        // La suppression d'une catégorie supprimerait ses livres en cascade : on l'interdit tant qu'elle en contient.
        if ($category->books()->withTrashed()->exists()) {
            return back()->with('error', "Impossible de supprimer « {$category->name} » : déplacez d'abord ses livres vers une autre catégorie.");
        }

        $name = $category->name;
        $category->delete();

        AuditLog::log(auth()->id(), 'category_deleted', ['name' => $name]);

        return redirect()->route('admin.categories.index')->with('success', "La catégorie « {$name} » a été supprimée.");
    }

    protected function uniqueSlug(string $name, ?Category $ignore = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
