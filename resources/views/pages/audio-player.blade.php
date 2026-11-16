@extends('layouts.app')
@section('title', 'Écoute — '.$book->title.' — NSOROMMA')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    @php
        $languages = collect(config('audio.languages'))
            ->filter(fn (string $label, string $code) => $parts->contains(fn ($part) => $part->tracks->contains('language', $code)));
        $audioPlans = $book->audioPlans();
        $subscriptions = app(\App\Services\AudioSubscriptionService::class);
    @endphp

    {{-- Le lecteur se déclare à l'ouverture de la lecture (2 appareils au plus), puis signale l'écoute toutes les 20 s pour décompter le temps. --}}
    <div class="pg"
         x-data="{
            tracks: @js($unlockedTracks),
            heartbeats: @js($heartbeatUrls),
            remaining: @js($remainingSeconds),
            openUrl: @js(route('library.listen.open')),
            lang: @js($languages->keys()->first()),
            current: null,
            message: '',
            timer: null,
            retried: false,
            send(url) {
                return fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
            },
            async openDevice() {
                try { return (await this.send(this.openUrl)).ok; } catch (error) { return false; }
            },
            async play(partId) {
                const src = this.tracks[partId]?.[this.lang];
                if (! src) { return; }
                this.message = '';
                if (! await this.openDevice()) { this.message = `Impossible de lancer l’écoute. Vérifiez votre connexion et réessayez.`; return; }
                this.current = partId;
                this.retried = false;
                this.$refs.player.src = src;
                this.$refs.player.play();
            },
            async beat() {
                const url = this.heartbeats[this.current]?.[this.lang];
                if (! url) { return; }
                let response;
                try { response = await this.send(url); } catch (error) { return; }
                const data = await response.json().catch(() => ({}));
                if ('remaining_seconds' in data) { this.remaining[this.current] = data.remaining_seconds; }
                if (response.status === 409 || response.status === 403) { this.stop(data.message); }
            },
            startBeats() { this.stopBeats(); this.timer = setInterval(() => this.beat(), {{ \App\Services\ListeningSessionService::HEARTBEAT_SECONDS * 1000 }}); },
            stopBeats() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
            stop(message) {
                this.stopBeats();
                this.$refs.player.pause();
                if (this.current !== null && this.remaining[this.current] === 0) { delete this.tracks[this.current]; }
                this.current = null;
                this.message = message || '';
            },
            paused() { if (this.timer) { this.stopBeats(); this.beat(); } },
            async recover() {
                if (this.current === null || this.retried) { return; }
                this.retried = true;
                const position = this.$refs.player.currentTime;
                if (! await this.openDevice()) { return; }
                this.$refs.player.load();
                this.$refs.player.currentTime = position;
                this.$refs.player.play();
            },
            duration(seconds) {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                return hours > 0 ? `${hours} h ${String(minutes).padStart(2, '0')}` : `${minutes} min`;
            },
            switchLanguage() {
                if (this.current !== null) {
                    this.tracks[this.current]?.[this.lang] ? this.play(this.current) : (this.$refs.player.pause(), this.current = null);
                }
            },
         }">
        <header class="pg-head">
            <ol class="pg-crumbs">
                <li><a href="{{ route('library.index', ['onglet' => 'ecoute']) }}">Ma bibliothèque</a></li>
                <li>Écoute audio</li>
            </ol>
            <h1 class="pg-title">{{ $book->title }}</h1>
            <p class="pg-subtitle">{{ $book->author }}</p>
        </header>

        @if($languages->isNotEmpty())
            <div class="pg-audio-toolbar">
                <label for="listen-language" class="pg-owned-meta">Langue d'écoute</label>
                <select id="listen-language" class="pg-select" x-model="lang" @change="switchLanguage()">
                    @foreach($languages as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <p class="pg-alert" role="status" x-show="message" x-text="message" x-cloak style="display: none;"></p>

        <ol class="pg-audio-parts">
            @foreach($parts as $part)
                @php $endsAt = $accessEndsAt[$part->id]; @endphp
                <li class="pg-audio-part" :class="{ 'is-playing': current === {{ $part->id }} }">
                    <div class="pg-audio-part-head">
                        <span class="pg-owned-meta">Partie {{ $part->position }}</span>
                        <h2 class="pg-owned-title" style="margin-top: .2rem;">{{ $part->title }}</h2>
                        @if($part->summary)
                            <p class="pg-hint" style="margin: .4rem 0 0;">{{ $part->summary }}</p>
                        @endif
                        <p class="pg-hint" style="margin: .5rem 0 0;">
                            @if($endsAt)
                                Accès jusqu'au {{ $endsAt->format('d/m/Y à H:i') }}
                                @if($remainingSeconds[$part->id] !== null)
                                    · <span x-text="duration(remaining[{{ $part->id }}]) + ' d’écoute restantes'">{{ intdiv($remainingSeconds[$part->id], 3600) }} h d’écoute restantes</span>
                                @endif
                            @else
                                Non abonné
                            @endif
                        </p>
                    </div>

                    <div class="pg-audio-part-buy">
                        @if(isset($unlockedTracks[$part->id]))
                            <button type="button" class="pg-btn" x-show="tracks[{{ $part->id }}]?.[lang]" @click="play({{ $part->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                                <span x-text="current === {{ $part->id }} ? 'En cours' : 'Écouter'">Écouter</span>
                            </button>
                            <span class="pg-hint" x-show="! tracks[{{ $part->id }}]?.[lang]" x-text="remaining[{{ $part->id }}] === 0 ? 'Temps d’écoute épuisé' : 'Pas encore disponible dans cette langue'" x-cloak style="display: none;">Pas encore disponible dans cette langue</span>
                        @elseif($endsAt && $remainingSeconds[$part->id] === 0)
                            <span class="pg-hint">Temps d’écoute épuisé</span>
                        @endif

                        @if(! $endsAt || $endsAt->diffInDays(now(), true) < 7 || $remainingSeconds[$part->id] === 0)
                            @if($audioPlans !== [] && $book->is_audio_available)
                                <form action="{{ route('cart.add') }}" method="POST" data-live-stay data-fly-to-cart class="pg-audio-renew">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <input type="hidden" name="format" value="audio">
                                    <input type="hidden" name="book_part_id" value="{{ $part->id }}">
                                    <label class="sr-only" for="renew_{{ $part->id }}">Durée d'abonnement</label>
                                    <select id="renew_{{ $part->id }}" name="access_days" class="pg-select">
                                        @foreach($audioPlans as $days => $price)
                                            <option value="{{ $days }}">{{ config('audio.plans.'.$days) }} — {{ number_format($price, 0, ',', ' ') }} FCFA{{ ($hours = $subscriptions->planHours($days)) ? ' · '.$hours.' h d’écoute' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="pg-btn {{ $endsAt ? 'pg-btn-ghost' : '' }}">{{ $endsAt ? 'Prolonger' : 'S\'abonner' }}</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="pg-audio-dock" x-show="current !== null" x-cloak style="display: none;">
            <audio x-ref="player" controls controlsList="nodownload noremoteplayback" disableRemotePlayback preload="none"
                   x-on:playing="startBeats()" x-on:play="openDevice()" x-on:pause="paused()" x-on:ended="paused(); current = null" x-on:error="recover()" x-on:contextmenu.prevent></audio>
        </div>
    </div>
@endsection
