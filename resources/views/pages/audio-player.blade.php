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
    @endphp

    <div class="pg"
         x-data="{
            tracks: @js($unlockedTracks),
            lang: @js($languages->keys()->first()),
            current: null,
            play(partId) {
                const src = this.tracks[partId]?.[this.lang];
                if (! src) { return; }
                this.current = partId;
                this.$refs.player.src = src;
                this.$refs.player.play();
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
                            <span class="pg-hint" x-show="! tracks[{{ $part->id }}]?.[lang]" x-cloak style="display: none;">Pas encore disponible dans cette langue</span>
                        @endif

                        @if(! $endsAt || $endsAt->diffInDays(now(), true) < 7)
                            @if($audioPlans !== [] && $book->is_audio_available)
                                <form action="{{ route('cart.add') }}" method="POST" data-live-stay data-fly-to-cart class="pg-audio-renew">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <input type="hidden" name="format" value="audio">
                                    <input type="hidden" name="book_part_id" value="{{ $part->id }}">
                                    <label class="sr-only" for="renew_{{ $part->id }}">Durée d'abonnement</label>
                                    <select id="renew_{{ $part->id }}" name="access_days" class="pg-select">
                                        @foreach($audioPlans as $days => $price)
                                            <option value="{{ $days }}">{{ config('audio.plans.'.$days) }} — {{ number_format($price, 0, ',', ' ') }} FCFA</option>
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
            <audio x-ref="player" controls controlsList="nodownload" preload="none" @ended="current = null" oncontextmenu="return false;"></audio>
        </div>
    </div>
@endsection
