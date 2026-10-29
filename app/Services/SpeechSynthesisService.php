<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class SpeechSynthesisService
{
    protected const ENDPOINT = 'https://api.openai.com/v1/audio/speech';

    public function isConfigured(): bool
    {
        return filled(config('services.openai.key'));
    }

    /**
     * Langues que la synthèse sait lire : code => libellé.
     *
     * @return array<string, string>
     */
    public function languages(): array
    {
        return array_intersect_key(config('audio.languages'), config('audio.synthesis.languages'));
    }

    /**
     * Lit le texte et écrit le MP3 obtenu dans le fichier indiqué.
     * L'API limite la longueur de chaque requête : le texte est lu morceau par morceau, puis les MP3 sont mis bout à bout.
     */
    public function synthesizeToFile(string $text, string $language, string $targetPath): void
    {
        $file = fopen($targetPath, 'wb');

        try {
            foreach ($this->chunks($text) as $chunk) {
                fwrite($file, $this->speak($chunk, $language));
            }
        } finally {
            fclose($file);
        }
    }

    /**
     * Découpe le texte en morceaux sous la limite de l'API, en coupant de préférence entre deux phrases.
     *
     * @return array<int, string>
     */
    public function chunks(string $text): array
    {
        $limit = (int) config('audio.synthesis.chunk_length');
        $sentences = preg_split('/(?<=[.!?…»:;])\s+|\R+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            // Phrase plus longue que la limite : coupée entre deux mots
            $pieces = mb_strlen($sentence) > $limit
                ? explode("\n", wordwrap($sentence, $limit, "\n", true))
                : [$sentence];

            foreach ($pieces as $piece) {
                if ($current !== '' && mb_strlen($current) + 1 + mb_strlen($piece) > $limit) {
                    $chunks[] = $current;
                    $current = '';
                }

                $current = $current === '' ? $piece : $current.' '.$piece;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * @throws RequestException
     */
    protected function speak(string $text, string $language): string
    {
        return Http::withToken(config('services.openai.key'))
            ->timeout(180)
            ->retry(3, 3000, fn (\Throwable $exception) => $exception instanceof ConnectionException
                || ($exception instanceof RequestException && ($exception->response->serverError() || $exception->response->status() === 429)))
            ->post(self::ENDPOINT, [
                'model' => config('audio.synthesis.model'),
                'voice' => config('audio.synthesis.voice'),
                'input' => $text,
                'instructions' => config('audio.synthesis.languages.'.$language),
                'response_format' => 'mp3',
            ])
            ->throw()
            ->body();
    }
}
