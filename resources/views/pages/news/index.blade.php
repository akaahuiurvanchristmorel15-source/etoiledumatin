@extends('layouts.app')
@section('title', ($selectedType ? $selectedType->pluralLabel().' — ' : '').'Actualités — NSOROMMA')
@section('meta_description', 'Événements, sorties de livres, conférences et annonces de la librairie panafricaine NSOROMMA.')
@section('canonical', route('news.index', array_filter(['type' => $selectedType?->value, 'page' => $news->currentPage() > 1 ? $news->currentPage() : null])))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}?v={{ filemtime(public_path('css/news.css')) }}">
@endpush

@section('content')
    <div class="news" data-live="news">
        <header class="news-header">
            <p class="news-eyebrow">Actualités</p>
            <h1 class="news-title">{{ $selectedType?->pluralLabel() ?? 'La vie de NSOROMMA' }}</h1>
            <p class="news-subtitle">Événements, sorties de livres, conférences et annonces : suivez l'actualité de la librairie et de ses auteurs.</p>

            <ul class="news-filters" aria-label="Filtrer par type">
                <li><a href="{{ route('news.index') }}" class="news-filter {{ ! $selectedType ? 'is-active' : '' }}" @if(! $selectedType) aria-current="page" @endif>Toutes</a></li>
                @foreach(\App\Enums\NewsType::cases() as $type)
                    <li>
                        <a href="{{ route('news.index', ['type' => $type->value]) }}" class="news-filter {{ $selectedType === $type ? 'is-active' : '' }}" @if($selectedType === $type) aria-current="page" @endif>{{ $type->pluralLabel() }}</a>
                    </li>
                @endforeach
            </ul>
        </header>

        <div class="news-layout {{ $upcomingEvents->isNotEmpty() ? 'has-aside' : '' }}">
            <section aria-label="Liste des actualités">
                @if($news->isNotEmpty())
                    <ul class="news-grid">
                        @foreach($news as $item)
                            <li>@include('pages.news._card', ['item' => $item])</li>
                        @endforeach
                    </ul>

                    {{ $news->onEachSide(1)->links('components.pagination') }}
                @else
                    <div class="news-empty">
                        <p class="news-empty-title">Aucune actualité pour le moment</p>
                        <p>Revenez bientôt ou <a href="{{ route('news.index') }}">consultez toutes les actualités</a>.</p>
                    </div>
                @endif
            </section>

            @if($upcomingEvents->isNotEmpty())
                <aside class="news-aside" aria-label="Agenda">
                    <h2 class="news-aside-title">À venir</h2>
                    <ul class="news-agenda">
                        @foreach($upcomingEvents as $event)
                            <li>
                                <a href="{{ route('news.show', $event->slug) }}">
                                    <span class="news-agenda-date">
                                        <strong>{{ $event->event_date->format('d') }}</strong>
                                        <small>{{ $event->event_date->locale('fr')->translatedFormat('M') }}</small>
                                    </span>
                                    <span>
                                        <span class="news-agenda-name">{{ $event->title }}</span>
                                        @if($event->location)<span class="news-agenda-place">{{ $event->location }}</span>@endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>
    </div>
@endsection
