<?php

namespace App\Services;

use App\Models\AudioTrack;
use App\Models\ListeningSession;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Diffusion d'une piste par petits morceaux, au rythme de l'écoute : le fichier entier ne peut pas être aspiré d'un coup.
 */
class AudioStreamService
{
    /**
     * Taille maximale d'une réponse : environ une minute d'audio à 128 kbit/s.
     */
    public const CHUNK_BYTES = 1048576;

    /**
     * Vitesse à laquelle la réserve de lecture se recharge, en secondes d'audio par seconde réelle.
     * Au-dessus de la vitesse de lecture (lecture accélérée, mise en mémoire tampon du navigateur), bien en dessous d'un téléchargement.
     */
    private const CREDIT_REFILL_RATE = 2.5;

    /**
     * Débit supposé quand il ne peut pas être lu dans le fichier : 320 kbit/s, le plus élevé d'un MP3.
     */
    private const FALLBACK_BYTES_PER_SECOND = 40000;

    /**
     * Débits des MP3 « Layer III » en kbit/s, selon l'index lu dans l'en-tête de trame.
     */
    private const MPEG1_BITRATES = [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320];

    private const MPEG2_BITRATES = [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160];

    /**
     * Plage d'octets à envoyer : celle demandée par le lecteur, ramenée à un morceau ; le début du fichier sans demande précise.
     *
     * @return array{0: int, 1: int}|null null si la plage demandée est hors du fichier
     */
    public function chunkRange(Request $request, int $size): ?array
    {
        if ($size === 0) {
            return null;
        }

        $start = 0;
        $end = $size - 1;

        if (preg_match('/^bytes=(\d*)-(\d*)/', (string) $request->headers->get('Range'), $matches)) {
            if ($matches[1] === '' && $matches[2] !== '') {
                $start = max(0, $size - (int) $matches[2]);
            } else {
                $start = (int) $matches[1];
                $end = $matches[2] !== '' ? min((int) $matches[2], $size - 1) : $size - 1;
            }
        }

        if ($start >= $size || $start > $end) {
            return null;
        }

        return [$start, min($end, $start + self::CHUNK_BYTES - 1)];
    }

    /**
     * Prélève le morceau sur la réserve de lecture de l'appareil ; refuse s'il demande l'audio plus vite qu'on ne l'écoute.
     */
    public function takeCredit(ListeningSession $listening, AudioTrack $track, int $bytes): bool
    {
        $elapsed = $listening->credit_updated_at ? max(0, $listening->credit_updated_at->diffInSeconds(now(), true)) : 0;
        $credit = min(
            ListeningSessionService::STREAM_CREDIT_SECONDS,
            $listening->stream_credit_seconds + $elapsed * self::CREDIT_REFILL_RATE
        );
        $cost = $bytes / $this->bytesPerSecond($track);

        if ($cost > $credit) {
            $listening->update(['stream_credit_seconds' => $credit, 'credit_updated_at' => now()]);

            return false;
        }

        $listening->update(['stream_credit_seconds' => $credit - $cost, 'credit_updated_at' => now()]);

        return true;
    }

    /**
     * Débit de la piste en octets par seconde d'audio, lu une fois dans son en-tête (MP3 ou WAV) puis gardé en cache.
     */
    public function bytesPerSecond(AudioTrack $track): int
    {
        $disk = Storage::disk(config('audio.disk'));

        return (int) Cache::rememberForever(
            'audio-bytes-per-second:'.$track->id.':'.$disk->lastModified($track->path),
            fn () => $this->readBytesPerSecond($disk, $track->path) ?? self::FALLBACK_BYTES_PER_SECOND
        );
    }

    private function readBytesPerSecond(Filesystem $disk, string $path): ?int
    {
        $stream = $disk->readStream($path);

        if (! $stream) {
            return null;
        }

        try {
            $head = (string) fread($stream, 10);

            if (str_starts_with($head, 'RIFF')) {
                $head .= (string) fread($stream, 22);

                // En-tête WAV : le débit en octets par seconde est écrit à l'octet 28
                return str_contains($head, 'WAVE') ? (unpack('V', substr($head, 28, 4))[1] ?: null) : null;
            }

            // Balise ID3v2 en tête de MP3 : sa taille est codée sur 4 octets de 7 bits
            if (str_starts_with($head, 'ID3') && strlen($head) === 10) {
                $tagSize = ((ord($head[6]) & 0x7F) << 21) | ((ord($head[7]) & 0x7F) << 14) | ((ord($head[8]) & 0x7F) << 7) | (ord($head[9]) & 0x7F);
                fseek($stream, 10 + $tagSize + ((ord($head[5]) & 0x10) ? 10 : 0));
                $head = '';
            }

            return $this->mp3BytesPerSecond($head.(string) fread($stream, 65536));
        } finally {
            fclose($stream);
        }
    }

    private function mp3BytesPerSecond(string $bytes): ?int
    {
        $length = strlen($bytes) - 3;

        for ($offset = 0; $offset < $length; $offset++) {
            $second = ord($bytes[$offset + 1]);

            if (ord($bytes[$offset]) !== 0xFF || ($second & 0xE0) !== 0xE0) {
                continue;
            }

            $version = ($second >> 3) & 0x03;
            $layer = ($second >> 1) & 0x03;
            $bitrateIndex = (ord($bytes[$offset + 2]) >> 4) & 0x0F;

            // Layer III uniquement, version et débit valides
            if ($layer !== 0x01 || $version === 0x01 || $bitrateIndex === 0 || $bitrateIndex === 0x0F) {
                continue;
            }

            $kbps = ($version === 0x03 ? self::MPEG1_BITRATES : self::MPEG2_BITRATES)[$bitrateIndex];

            return intdiv($kbps * 1000, 8);
        }

        return null;
    }
}
