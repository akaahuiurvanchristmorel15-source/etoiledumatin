<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSpeechTrack;
use App\Models\AudioSynthesis;
use App\Models\BookPart;
use App\Services\SpeechSynthesisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpeechSynthesisController extends Controller
{
    /**
     * Enregistre le texte d'une partie dans une langue et, sur demande, lance la génération de la voix.
     */
    public function update(Request $request, BookPart $part, string $language, SpeechSynthesisService $speech): RedirectResponse
    {
        abort_unless(array_key_exists($language, $speech->languages()), 404);

        $validated = $request->validateWithBag('synthesis', [
            'text' => ['nullable', 'string', 'max:500000'],
            'generate' => ['nullable', Rule::in(['1'])],
        ], [], ['text' => 'texte']);

        $part->update(['texts' => array_merge($part->texts ?? [], [$language => trim((string) ($validated['text'] ?? ''))])]);

        $languageLabel = $speech->languages()[$language];
        $redirect = redirect()->to(route('admin.books.edit', $part->book).'#parties-audio');

        if (! $request->boolean('generate')) {
            return $redirect->with('success', "Texte {$languageLabel} de la {$part->label()} enregistré.");
        }

        if (! $speech->isConfigured()) {
            return $redirect->with('error', 'La synthèse vocale n\'est pas configurée : ajoutez OPENAI_API_KEY au fichier .env.');
        }

        if ($part->text($language) === '') {
            return $redirect->with('error', "Collez d'abord le texte {$languageLabel} à lire.");
        }

        $synthesis = AudioSynthesis::firstOrNew(['book_part_id' => $part->id, 'language' => $language]);

        if ($synthesis->exists && $synthesis->isRunning()) {
            return $redirect->with('info', "La voix {$languageLabel} de la {$part->label()} est déjà en cours de génération.");
        }

        $synthesis->fill(['status' => 'pending', 'error' => null])->save();

        GenerateSpeechTrack::dispatch($part, $language);

        return $redirect->with('success', "Génération de la voix {$languageLabel} lancée pour la {$part->label()}. La piste remplacera l'actuelle une fois prête.");
    }
}
