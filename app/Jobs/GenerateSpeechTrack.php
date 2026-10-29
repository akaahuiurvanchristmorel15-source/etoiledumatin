<?php

namespace App\Jobs;

use App\Models\AudioSynthesis;
use App\Models\AuditLog;
use App\Models\BookPart;
use App\Services\SpeechSynthesisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Génère la piste d'une partie dans une langue à partir de son texte, puis remplace la piste existante.
 */
class GenerateSpeechTrack implements ShouldQueue
{
    use Queueable;

    /** Un livre entier peut demander plusieurs dizaines d'appels à l'API */
    public int $timeout = 1800;

    /** Pas de nouvel essai automatique : chaque génération est facturée */
    public int $tries = 1;

    public function __construct(public BookPart $part, public string $language) {}

    public function handle(SpeechSynthesisService $speech): void
    {
        $synthesis = $this->synthesis();
        $synthesis->update(['status' => 'processing', 'error' => null]);

        $disk = Storage::disk(config('audio.disk'));
        $path = config('audio.directory').'/'.Str::uuid().'.mp3';
        $disk->makeDirectory(config('audio.directory'));

        try {
            $speech->synthesizeToFile($this->part->text($this->language), $this->language, $disk->path($path));
        } catch (\Throwable $exception) {
            $disk->delete($path);

            throw $exception;
        }

        $this->part->tracks()->where('language', $this->language)->get()->each->delete();
        $this->part->tracks()->create(['language' => $this->language, 'path' => $path]);

        $synthesis->update(['status' => 'done']);

        AuditLog::log(null, 'audio_track_synthesized', ['part_id' => $this->part->id, 'language' => $this->language]);
    }

    public function failed(?\Throwable $exception): void
    {
        $this->synthesis()->update([
            'status' => 'failed',
            'error' => Str::limit((string) $exception?->getMessage(), 500),
        ]);
    }

    protected function synthesis(): AudioSynthesis
    {
        return AudioSynthesis::firstOrCreate(['book_part_id' => $this->part->id, 'language' => $this->language]);
    }
}
