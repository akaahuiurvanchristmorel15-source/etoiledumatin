<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Plan du site pour les moteurs de recherche : pages publiques, collections et livres publiés.
     */
    public function sitemap(): Response
    {
        $books = Book::active()->select(['slug', 'updated_at'])->latest('updated_at')->get();

        $categories = Category::where('is_active', true)
            ->whereHas('books', fn ($query) => $query->active())
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        $lastUpdate = $books->max('updated_at') ?? now();

        $urls = collect([
            ['loc' => route('home'), 'lastmod' => $lastUpdate, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('catalog.index'), 'lastmod' => $lastUpdate, 'changefreq' => 'daily', 'priority' => '0.9'],
        ])
            ->merge($categories->map(fn (Category $category) => [
                'loc' => route('catalog.index', ['category' => $category->slug]),
                'lastmod' => $category->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]))
            ->merge($books->map(fn (Book $book) => [
                'loc' => route('catalog.show', $book->slug),
                'lastmod' => $book->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Consignes aux robots : pages privées exclues, plan du site indiqué.
     * Hors production (local, recette), l'indexation est entièrement interdite.
     */
    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /panier',
                'Disallow: /commander',
                'Disallow: /ma-bibliotheque',
                'Disallow: /telechargement',
                'Disallow: /connexion',
                'Disallow: /inscription',
                'Disallow: /mot-de-passe-oublie',
                'Disallow: /reinitialiser-mot-de-passe',
                'Disallow: /email',
                'Disallow: /auth',
                'Disallow: /suivi-commande',
                'Disallow: /livre/*/apercu-pdf',
                'Disallow: /*?*search=',
                'Disallow: /*?*sort=',
                '',
                'Sitemap: '.route('seo.sitemap'),
            ]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
