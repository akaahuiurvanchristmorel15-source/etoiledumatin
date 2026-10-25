<?php

namespace Database\Seeders;

use App\Enums\NewsType;
use App\Models\Book;
use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Actualités de démonstration : sortie de livre, conférence, événement, annonce et un brouillon.
     */
    public function run(): void
    {
        $latestBook = Book::active()->latest()->first();

        News::create([
            'book_id' => $latestBook?->id,
            'title' => 'Nouvelle parution : '.($latestBook?->title ?? 'un nouveau titre au catalogue'),
            'slug' => 'nouvelle-parution',
            'type' => NewsType::BookRelease,
            'excerpt' => 'Un nouvel ouvrage rejoint le catalogue NSOROMMA, disponible en papier, en PDF et bientôt en audio.',
            'content' => "Nous avons la joie d'annoncer la parution d'un nouvel ouvrage au catalogue NSOROMMA.\n\nFruit de plusieurs années de recherche, ce livre prolonge notre engagement pour la transmission des savoirs africains. Il est disponible dès aujourd'hui en version papier et en e-Book PDF.",
            'published_at' => now()->subDays(2),
            'is_published' => true,
        ]);

        News::create([
            'title' => 'Conférence : philosophie africaine et modernité',
            'slug' => 'conference-philosophie-africaine-et-modernite',
            'type' => NewsType::Conference,
            'excerpt' => 'Une soirée d\'échanges avec nos auteurs autour de la pensée africaine contemporaine.',
            'content' => "Les Éditions NSOROMMA organisent une conférence-débat consacrée à la philosophie africaine face aux défis de la modernité.\n\nL'entrée est libre dans la limite des places disponibles. Une séance de dédicaces suivra les échanges.",
            'event_date' => now()->addWeeks(3)->setTime(18, 30),
            'location' => 'Abidjan, Cocody — Institut Goethe',
            'published_at' => now()->subWeek(),
            'is_published' => true,
        ]);

        News::create([
            'title' => 'NSOROMMA au Salon international du livre d\'Abidjan',
            'slug' => 'nsoromma-au-salon-international-du-livre-d-abidjan',
            'type' => NewsType::Event,
            'excerpt' => 'Retrouvez toute l\'équipe et nos auteurs sur notre stand pendant toute la durée du salon.',
            'content' => "Pour cette nouvelle édition du SILA, NSOROMMA vous accueille sur son stand avec l'ensemble du catalogue.\n\nVenez rencontrer nos auteurs, découvrir nos nouveautés et profiter de remises exclusives sur les livres papier.",
            'event_date' => now()->addMonth()->setTime(10, 0),
            'location' => 'Abidjan, Parc des expositions',
            'published_at' => now()->subDays(10),
            'is_published' => true,
        ]);

        News::create([
            'title' => 'Livraison offerte pendant tout le mois',
            'slug' => 'livraison-offerte-pendant-tout-le-mois',
            'type' => NewsType::Other,
            'excerpt' => 'Profitez de la livraison gratuite à Abidjan pour toute commande de livres papier.',
            'content' => "Pour célébrer l'anniversaire de la librairie, la livraison des livres papier est offerte à Abidjan pendant tout le mois.\n\nAucun code n'est nécessaire : la remise s'applique automatiquement à la commande.",
            'published_at' => now()->subDays(20),
            'is_published' => true,
        ]);

        News::create([
            'title' => 'Prochaine rencontre d\'auteurs (brouillon)',
            'slug' => 'prochaine-rencontre-d-auteurs',
            'type' => NewsType::Event,
            'excerpt' => 'Annonce en préparation.',
            'content' => 'Programme à confirmer.',
            'is_published' => false,
        ]);
    }
}
