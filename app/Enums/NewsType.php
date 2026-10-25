<?php

namespace App\Enums;

enum NewsType: string
{
    case Event = 'event';
    case BookRelease = 'book_release';
    case Conference = 'conference';
    case Other = 'other';

    /**
     * Libellé affiché aux lecteurs et dans l'administration.
     */
    public function label(): string
    {
        return match ($this) {
            self::Event => 'Événement',
            self::BookRelease => 'Sortie de livre',
            self::Conference => 'Conférence',
            self::Other => 'Annonce',
        };
    }

    /**
     * Libellé au pluriel pour les filtres de la page Actualités.
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Event => 'Événements',
            self::BookRelease => 'Sorties de livres',
            self::Conference => 'Conférences',
            self::Other => 'Annonces',
        };
    }
}
