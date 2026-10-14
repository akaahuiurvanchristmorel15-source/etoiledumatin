@extends('layouts.admin')
@section('title', 'Commande ' . $order->order_number . ' — Administration')

@section('content')
<div style="max-width: 1050px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- En-tête -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
        <div>
            <nav class="breadcrumb" style="margin-bottom: 0.5rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Commandes</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
                </ol>
            </nav>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <h1 class="hero-title font-editorial" style="font-size: 2.2rem; line-height: 1.15; margin: 0;">
                    Commande {{ $order->order_number }}
                </h1>
                @if($order->payment_status === 'paid')
                    <span class="admin-badge admin-badge-green">Paiement Validé</span>
                @else
                    <span class="admin-badge admin-badge-gold">{{ ucfirst($order->payment_status) }}</span>
                @endif
            </div>
            <p style="font-size: 0.85rem; color: #786B5A; margin-top: 0.25rem;">
                Enregistrée le {{ $order->created_at->format('d/m/Y à H:i') }} &bull; Réf. Paystack : <strong>{{ $order->payment_reference ?? 'N/A' }}</strong>
            </p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="admin-btn-secondary">
            &larr; Retour aux commandes
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; align-items: start;">

        <!-- Colonne Gauche : Articles & Droits Numériques -->
        <div style="display: flex; flex-direction: column; gap: 1.75rem;">
            
            <!-- Articles Commandés -->
            <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
                <h2 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                    Articles Achetés ({{ $order->items->count() }})
                </h2>

                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    @foreach($order->items as $item)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(139, 133, 124, 0.08);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 48px; height: 72px; border-radius: 8px; overflow: hidden; background: #F8F1E5; border: 1px solid rgba(217, 174, 92, 0.2); flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                    <img src="{{ $item->book?->cover_image ? asset($item->book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="{{ $item->book_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #1E1D1B; font-size: 0.95rem;">{{ $item->book_title }}</div>
                                    <div style="font-size: 0.78rem; color: #8B857C;">{{ $item->book_author }}</div>
                                    <div style="margin-top: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                                        @if($item->format === 'pdf')
                                            <span class="admin-badge admin-badge-gold">e-Book PDF</span>
                                        @elseif($item->format === 'audio')
                                            <span class="admin-badge" style="background: rgba(108, 52, 131, 0.15); color: #6C3483;">Livre Audio MP3</span>
                                        @else
                                            <span class="admin-badge" style="background: rgba(30, 29, 27, 0.08); color: #1E1D1B;">Livre Papier Relié</span>
                                        @endif
                                        <span style="font-size: 0.75rem; color: #8B857C;">&times; {{ $item->quantity }}</span>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">{{ number_format((float) ($item->subtotal ?? 0), 0, ',', ' ') }} FCFA</div>
                                <div style="font-size: 0.72rem; color: #8B857C;">{{ number_format((float) ($item->unit_price ?? 0), 0, ',', ' ') }} FCFA / u</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Récapitulatif Financier -->
                <div style="padding-top: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                    @if($order->subtotal_pdf > 0)
                        <div style="display: flex; justify-content: space-between; color: #786B5A;">
                            <span>Sous-total e-Books PDF</span>
                            <span style="font-weight: 700; color: #1E1D1B;">{{ number_format((float) ($order->subtotal_pdf ?? 0), 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if(($order->subtotal_audio ?? 0) > 0)
                        <div style="display: flex; justify-content: space-between; color: #786B5A;">
                            <span>Sous-total Livres Audio</span>
                            <span style="font-weight: 700; color: #6C3483;">{{ number_format((float) ($order->subtotal_audio ?? 0), 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($order->subtotal_physical > 0)
                        <div style="display: flex; justify-content: space-between; color: #786B5A;">
                            <span>Sous-total Livres physiques</span>
                            <span style="font-weight: 700; color: #1E1D1B;">{{ number_format((float) ($order->subtotal_physical ?? 0), 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; color: #786B5A;">
                        <span>Frais d'acheminement livraison</span>
                        <span style="font-weight: 700; color: #1E1D1B;">{{ number_format((float) ($order->shipping_cost ?? 0), 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; color: #1E1D1B; padding-top: 0.75rem; border-top: 1.5px solid rgba(217, 174, 92, 0.3);">
                        <span class="font-editorial">Total Encaissé</span>
                        <span style="color: #D9AE5C;">{{ number_format((float) ($order->total_amount ?? 0), 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>

            <!-- E-books & Téléchargements Générés -->
            @if($order->downloads->isNotEmpty())
                <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
                    <h2 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                        Droits Numériques &amp; Audio
                    </h2>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($order->downloads as $download)
                            <div style="padding: 1rem 1.25rem; background: #FAF5ED; border-radius: 14px; border: 1px solid rgba(217, 174, 92, 0.2); display: flex; align-items: center; justify-content: space-between; font-size: 0.82rem;">
                                <div>
                                    <div style="font-weight: 700; color: #1E1D1B;">
                                        {{ $download->book?->title ?? 'Ouvrage' }}
                                        <span class="admin-badge" style="margin-left: 0.5rem; font-size: 0.65rem; background: {{ $download->isAudio() ? 'rgba(108, 52, 131, 0.15); color: #6C3483;' : 'rgba(47, 111, 78, 0.15); color: #2F6F4E;' }}">
                                            {{ $download->isAudio() ? 'Audio MP3' : 'e-Book PDF' }}
                                        </span>
                                    </div>
                                    <div style="font-family: monospace; font-size: 0.72rem; color: #8B857C; margin-top: 2px;">Token: {{ substr($download->download_token, 0, 16) }}...</div>
                                </div>
                                <span class="admin-badge {{ $download->download_count < $download->max_downloads ? 'admin-badge-green' : 'admin-badge-red' }}">
                                    {{ $download->download_count }} / {{ $download->max_downloads }} téléchargements
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Colonne Droite : Fiche Client & Gestion Expédition -->
        <div style="display: flex; flex-direction: column; gap: 1.75rem;">

            <!-- Fiche Client -->
            <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04); display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.85rem;">
                <h2 class="font-editorial" style="font-size: 1.15rem; font-weight: 800; color: #1E1D1B; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
                    Coordonnées Client
                </h2>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #8B857C; display: block;">Nom &amp; Prénoms</span>
                    <span style="font-weight: 700; color: #1E1D1B; font-size: 0.95rem;">{{ $order->customer_name }}</span>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #8B857C; display: block;">Email</span>
                    <a href="mailto:{{ $order->customer_email }}" style="color: #D9AE5C; font-weight: 700; text-decoration: underline;">{{ $order->customer_email }}</a>
                </div>
                <div>
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #8B857C; display: block;">Téléphone</span>
                    <span style="font-family: monospace; color: #1E1D1B; font-weight: 700;">{{ $order->customer_phone }}</span>
                </div>
                <div style="padding-top: 0.5rem; border-top: 1px solid rgba(139, 133, 124, 0.08);">
                    @if($order->user)
                        <span style="font-size: 0.75rem; color: #2F6F4E; font-weight: 700;">
                            ✓ Client avec compte enregistré (ID #{{ $order->user->id }})
                        </span>
                    @else
                        <span style="font-size: 0.75rem; color: #8B857C;">
                            Commande passée en mode Invité
                        </span>
                    @endif
                </div>
            </div>

            <!-- Gestion Expédition -->
            <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04); font-size: 0.85rem;">
                <h2 class="font-editorial" style="font-size: 1.15rem; font-weight: 800; color: #1E1D1B; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1); margin-bottom: 1rem;">
                    Logistique &amp; Envoi
                </h2>

                @if($order->shippingAddress)
                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #8B857C; display: block;">Adresse de livraison</span>
                        <p style="font-weight: 700; color: #1E1D1B; margin-top: 0.25rem;">{{ $order->shippingAddress->address_line }}</p>
                        <p style="color: #786B5A;">{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->country }}</p>
                        @if($order->shippingAddress->notes)
                            <p style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: #FAF5ED; border-radius: 8px; border: 1px solid rgba(217, 174, 92, 0.25); font-size: 0.78rem; color: #786B5A;">
                                <strong>Indications :</strong> {{ $order->shippingAddress->notes }}
                            </p>
                        @endif
                    </div>

                    <!-- Formulaire de mise à jour statut expédition -->
                    <form action="{{ route('admin.orders.update-shipping', $order) }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem; padding-top: 1rem; border-top: 1px solid rgba(139, 133, 124, 0.1);">
                        @csrf

                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #1E1D1B; margin-bottom: 0.35rem;">Statut d'expédition</label>
                            <select name="status" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                                <option value="pending" {{ $order->shippingAddress->status === 'pending' ? 'selected' : '' }}>À préparer</option>
                                <option value="processing" {{ $order->shippingAddress->status === 'processing' ? 'selected' : '' }}>En préparation</option>
                                <option value="shipped" {{ $order->shippingAddress->status === 'shipped' ? 'selected' : '' }}>Expédié / En transit</option>
                                <option value="delivered" {{ $order->shippingAddress->status === 'delivered' ? 'selected' : '' }}>Colis Livré</option>
                                <option value="cancelled" {{ $order->shippingAddress->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #1E1D1B; margin-bottom: 0.35rem;">Transporteur</label>
                            <input type="text" name="carrier_name" value="{{ old('carrier_name', $order->shippingAddress->carrier_name) }}" placeholder="Ex : Chronopost, Livro, Coursier interne..." style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #1E1D1B; margin-bottom: 0.35rem;">N° de Suivi</label>
                            <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->shippingAddress->tracking_number) }}" placeholder="Ex : TRK-98234-CI" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                        </div>

                        <button type="submit" class="admin-btn-primary" style="width: 100%; justify-content: center; margin-top: 0.25rem;">
                            Mettre à jour le statut
                        </button>
                    </form>
                @else
                    <p style="text-align: center; color: #8B857C; padding: 1rem 0; font-size: 0.82rem;">
                        Cette commande est 100% numérique (e-Books). Aucun envoi physique n'est requis.
                    </p>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
