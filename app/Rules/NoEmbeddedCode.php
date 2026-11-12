<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class NoEmbeddedCode implements ValidationRule
{
    /**
     * Balises qui trahissent un fichier piégé : du code PHP ou du JavaScript caché dans une image ou un son (fichier « polyglotte »).
     */
    private const SUSPICIOUS_MARKERS = ['<?php', '<script'];

    /**
     * Taille des blocs lus : le fichier est analysé sans être chargé entièrement en mémoire.
     */
    private const CHUNK_BYTES = 1048576;

    /**
     * Refuse un fichier envoyé qui contient du code exécutable, quelle que soit son extension.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        if ($this->containsSuspiciousMarker((string) $value->getRealPath())) {
            $fail('Le fichier :attribute contient du code et a été refusé.');
        }
    }

    private function containsSuspiciousMarker(string $path): bool
    {
        // Un fichier illisible (bloqué par un antivirus, par exemple) est refusé plutôt qu'accepté sans contrôle
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return true;
        }

        // Le chevauchement entre deux blocs évite de rater une balise coupée en deux
        $overlap = max(array_map('strlen', self::SUSPICIOUS_MARKERS)) - 1;
        $previousTail = '';

        try {
            while (! feof($handle)) {
                $chunk = $previousTail.strtolower((string) fread($handle, self::CHUNK_BYTES));

                foreach (self::SUSPICIOUS_MARKERS as $marker) {
                    if (str_contains($chunk, $marker)) {
                        return true;
                    }
                }

                $previousTail = substr($chunk, -$overlap);
            }
        } finally {
            fclose($handle);
        }

        return false;
    }
}
