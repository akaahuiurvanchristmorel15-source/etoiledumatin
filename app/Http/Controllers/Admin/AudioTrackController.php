<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioTrack;
use App\Models\AuditLog;
use App\Models\BookPart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AudioTrackController extends Controller
{
    /**
     * Importe la piste d'une partie dans une langue ; une piste existante pour cette langue est remplacée.
     */
    public function store(Request $request, BookPart $part): RedirectResponse
    {
        $validated = $request->validateWithBag('track', [
            'language' => ['required', Rule::in(array_keys(config('audio.languages')))],
            'audio_file' => ['required', 'file', 'mimes:mp3,m4a,aac,ogg,wav', 'max:204800'],
        ], [], ['language' => 'langue', 'audio_file' => 'fichier audio']);

        $part->tracks()->where('language', $validated['language'])->get()->each->delete();

        $track = $part->tracks()->create([
            'language' => $validated['language'],
            'path' => $request->file('audio_file')->store(config('audio.directory'), config('audio.disk')),
        ]);

        AuditLog::log(auth()->id(), 'audio_track_uploaded', ['part_id' => $part->id, 'language' => $track->language]);

        return redirect()->to(route('admin.books.edit', $part->book).'#parties-audio')
            ->with('success', "Piste {$track->languageLabel()} enregistrée pour la {$part->label()}.");
    }

    public function destroy(AudioTrack $track): RedirectResponse
    {
        $track->delete();

        return redirect()->to(route('admin.books.edit', $track->part->book).'#parties-audio')
            ->with('success', "Piste {$track->languageLabel()} supprimée.");
    }
}
