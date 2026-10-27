<?php

namespace App\Http\Controllers;

use App\Models\AudioTrack;
use App\Models\Book;
use App\Services\AudioSubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioListeningController extends Controller
{
    /**
     * Lecteur d'un livre : toutes ses parties, écoutables pour celles dont l'abonnement est en cours.
     */
    public function show(Book $book, AudioSubscriptionService $subscriptions): View
    {
        $user = auth()->user();
        $parts = $book->parts()->with('tracks')->get();

        $accessEndsAt = $parts->mapWithKeys(fn ($part) => [$part->id => $subscriptions->accessEndsAt($user, $part)]);

        abort_unless($accessEndsAt->filter()->isNotEmpty() || $book->offersAudioSubscription(), 404);

        $unlockedTracks = $parts
            ->filter(fn ($part) => $accessEndsAt[$part->id] && $user->canListenTo($part))
            ->mapWithKeys(fn ($part) => [$part->id => $part->tracks->mapWithKeys(fn (AudioTrack $track) => [
                $track->language => route('library.listen.stream', $track),
            ])]);

        return view('pages.audio-player', compact('book', 'parts', 'accessEndsAt', 'unlockedTracks'));
    }

    /**
     * Diffuse la piste en streaming (requêtes partielles acceptées) aux seuls abonnés en cours.
     */
    public function stream(AudioTrack $track): BinaryFileResponse
    {
        abort_unless(auth()->user()->canListenTo($track->part), 403, 'Votre abonnement d\'écoute à cette partie est terminé.');

        $disk = Storage::disk(config('audio.disk'));

        abort_unless($disk->exists($track->path), 404);

        $response = response()->file($disk->path($track->path), [
            'Content-Type' => $disk->mimeType($track->path) ?: 'audio/mpeg',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        // Accès payant et limité dans le temps : aucun cache partagé ni local
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
