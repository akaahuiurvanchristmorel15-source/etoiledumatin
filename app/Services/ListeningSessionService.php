<?php

namespace App\Services;

use App\Models\ListeningSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Appareils en train d'écouter : deux au plus par abonné, le plus ancien laisse sa place au nouveau.
 * Un appareil correspond à un navigateur (sa session) : plusieurs onglets d'un même navigateur comptent pour un.
 */
class ListeningSessionService
{
    public const MAX_DEVICES = 2;

    /**
     * Intervalle des signaux envoyés par le lecteur pendant la lecture, en secondes.
     */
    public const HEARTBEAT_SECONDS = 20;

    /**
     * Sans signal depuis ce délai, l'appareil ne compte plus parmi ceux qui écoutent.
     */
    public const IDLE_SECONDS = 90;

    /**
     * Sans signal depuis ce délai, l'appareil doit relancer la lecture pour recevoir de l'audio.
     */
    public const STREAM_IDLE_SECONDS = 300;

    /**
     * Réserve de lecture d'un nouvel appareil, en secondes d'audio : de quoi démarrer sans attendre.
     */
    public const STREAM_CREDIT_SECONDS = 600;

    private const SESSION_KEY = 'listening_session_id';

    /**
     * Ouvre (ou reprend) l'écoute sur cet appareil, puis coupe les appareils les plus anciens au-delà de la limite.
     */
    public function open(User $user, Request $request): ListeningSession
    {
        $listening = $this->current($user, $request);

        if ($listening) {
            $listening->update(['last_seen_at' => now()]);
        } else {
            $listening = ListeningSession::create([
                'user_id' => $user->id,
                'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
                'ip_address' => $request->ip(),
                'started_at' => now(),
                'last_seen_at' => now(),
                'stream_credit_seconds' => self::STREAM_CREDIT_SECONDS,
                'credit_updated_at' => now(),
            ]);

            $request->session()->put(self::SESSION_KEY, $listening->id);
        }

        $this->releaseOldestDevices($user, $listening);

        return $listening;
    }

    /**
     * L'écoute de ce navigateur, si elle n'a pas été coupée.
     */
    public function current(User $user, Request $request): ?ListeningSession
    {
        $id = $request->session()->get(self::SESSION_KEY);

        return $id ? ListeningSession::where('user_id', $user->id)->whereNull('ended_at')->find($id) : null;
    }

    /**
     * L'écoute de ce navigateur a-t-elle été coupée par un autre appareil ?
     */
    public function wasReplaced(User $user, Request $request): bool
    {
        $id = $request->session()->get(self::SESSION_KEY);

        return $id && ListeningSession::where('user_id', $user->id)->whereKey($id)->where('end_reason', 'replaced')->exists();
    }

    /**
     * L'appareil reçoit-il encore de l'audio : il a donné signe de vie récemment.
     */
    public function canStream(ListeningSession $listening): bool
    {
        return $listening->last_seen_at->gt(now()->subSeconds(self::STREAM_IDLE_SECONDS));
    }

    private function releaseOldestDevices(User $user, ListeningSession $keep): void
    {
        ListeningSession::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->whereKeyNot($keep->id)
            ->where('last_seen_at', '>', now()->subSeconds(self::IDLE_SECONDS))
            ->orderByDesc('started_at')
            ->get()
            ->slice(self::MAX_DEVICES - 1)
            ->each(fn (ListeningSession $listening) => $listening->update(['ended_at' => now(), 'end_reason' => 'replaced']));
    }
}
