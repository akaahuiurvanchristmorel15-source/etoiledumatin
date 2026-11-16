<?php

namespace App\Http\Controllers;

use App\Models\AudioTrack;
use App\Models\Book;
use App\Services\AudioStreamService;
use App\Services\AudioSubscriptionService;
use App\Services\ListeningSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioListeningController extends Controller
{
    /**
     * Destinations de requête d'un lecteur audio : une ouverture directe de l'adresse ou un script sont refusés.
     */
    private const MEDIA_FETCH_DESTINATIONS = ['audio', 'video'];

    /**
     * Lecteur d'un livre : toutes ses parties, écoutables pour celles dont l'abonnement est en cours.
     */
    public function show(Book $book, AudioSubscriptionService $subscriptions): View
    {
        $user = auth()->user();
        $parts = $book->parts()->with('tracks')->get();

        $accessEndsAt = $parts->mapWithKeys(fn ($part) => [$part->id => $subscriptions->accessEndsAt($user, $part)]);
        $remainingSeconds = $parts->mapWithKeys(fn ($part) => [$part->id => $accessEndsAt[$part->id] ? $subscriptions->remainingSeconds($user, $part) : 0]);

        abort_unless($accessEndsAt->filter()->isNotEmpty() || $book->offersAudioSubscription(), 404);

        $listenableParts = $parts->filter(fn ($part) => $accessEndsAt[$part->id] && $user->canListenTo($part));

        $unlockedTracks = $listenableParts->mapWithKeys(fn ($part) => [$part->id => $part->tracks->mapWithKeys(fn (AudioTrack $track) => [
            $track->language => route('library.listen.stream', $track),
        ])]);

        $heartbeatUrls = $listenableParts->mapWithKeys(fn ($part) => [$part->id => $part->tracks->mapWithKeys(fn (AudioTrack $track) => [
            $track->language => route('library.listen.heartbeat', $track),
        ])]);

        return view('pages.audio-player', compact('book', 'parts', 'accessEndsAt', 'remainingSeconds', 'unlockedTracks', 'heartbeatUrls'));
    }

    /**
     * Lancement de la lecture sur cet appareil : au-delà de deux appareils, le plus ancien est coupé.
     */
    public function open(Request $request, ListeningSessionService $sessions): JsonResponse
    {
        $sessions->open($request->user(), $request);

        return response()->json(['heartbeat_seconds' => ListeningSessionService::HEARTBEAT_SECONDS]);
    }

    /**
     * Signal du lecteur pendant la lecture : décompte le temps écouté et arrête l'écoute si elle n'est plus permise.
     */
    public function heartbeat(
        Request $request,
        AudioTrack $track,
        ListeningSessionService $sessions,
        AudioSubscriptionService $subscriptions
    ): JsonResponse {
        $user = $request->user();

        if ($sessions->wasReplaced($user, $request) && ! $sessions->current($user, $request)) {
            return response()->json([
                'reason' => 'replaced',
                'message' => 'L\'écoute s\'est arrêtée : votre compte écoute déjà sur '.ListeningSessionService::MAX_DEVICES.' autres appareils.',
            ], 409);
        }

        $listening = $sessions->current($user, $request) ?? $sessions->open($user, $request);

        // Temps écoulé depuis le signal précédent, plafonné : une coupure réseau ou une pause ne sont pas facturées
        $elapsed = min(
            (int) $listening->last_seen_at->diffInSeconds(now(), true),
            ListeningSessionService::HEARTBEAT_SECONDS + 10
        );

        $subscriptions->consume($user, $track->part, $elapsed);
        $listening->update(['last_seen_at' => now()]);

        if (! $user->canListenTo($track->part)) {
            return response()->json([
                'reason' => 'quota',
                'message' => 'Votre temps d\'écoute pour cette partie est épuisé, ou votre abonnement est terminé.',
                'remaining_seconds' => 0,
            ], 403);
        }

        return response()->json(['remaining_seconds' => $subscriptions->remainingSeconds($user, $track->part)]);
    }

    /**
     * Diffuse la piste par morceaux (requêtes partielles), au rythme de l'écoute, aux seuls abonnés en cours.
     * Aucune copie complète n'est envoyée : sans lecteur ouvert sur cet appareil, rien n'est servi.
     */
    public function stream(
        Request $request,
        AudioTrack $track,
        ListeningSessionService $sessions,
        AudioStreamService $streams
    ): BinaryFileResponse {
        $user = $request->user();

        abort_unless($user->canListenTo($track->part), 403, 'Votre abonnement d\'écoute à cette partie est terminé, ou votre temps d\'écoute est épuisé.');

        $destination = $request->headers->get('Sec-Fetch-Dest');
        abort_if($destination !== null && ! in_array($destination, self::MEDIA_FETCH_DESTINATIONS, true), 403, 'Cette piste s\'écoute uniquement depuis le lecteur de votre bibliothèque.');

        $listening = $sessions->current($user, $request);
        abort_unless($listening && $sessions->canStream($listening), 403, 'Relancez la lecture depuis votre bibliothèque.');

        $disk = Storage::disk(config('audio.disk'));
        abort_unless($disk->exists($track->path), 404);

        $size = $disk->size($track->path);
        $range = $streams->chunkRange($request, $size);

        if ($range) {
            [$start, $end] = $range;

            abort_unless($streams->takeCredit($listening, $track, $end - $start + 1), 429, 'Lecture trop rapide : patientez quelques instants.');

            // La réponse partielle ne couvre qu'un morceau : le lecteur demande la suite au fil de l'écoute.
            // Un fichier qui tient en un seul morceau, demandé sans plage, est envoyé normalement.
            if ($request->headers->has('Range') || $size > AudioStreamService::CHUNK_BYTES) {
                $request->headers->set('Range', "bytes={$start}-{$end}");
                $request->headers->remove('If-Range');
            }
        }

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
