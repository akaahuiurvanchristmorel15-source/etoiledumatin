@extends('layouts.app')

@section('title', 'Simulation Paiement Paystack — NSOROMMA')
@section('meta_description', 'Environnement de simulation sécurisé pour les paiements Paystack.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div class="page-section-padded" style="max-width: 760px; margin: 0 auto;">
    
    <!-- Fil d'Ariane -->
    <nav class="breadcrumb" style="margin-bottom: 1.5rem;" aria-label="Fil d'Ariane">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
            <li class="breadcrumb-separator">›</li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Panier</a></li>
            <li class="breadcrumb-separator">›</li>
            <li class="breadcrumb-item active" aria-current="page">Simulation Paystack</li>
        </ol>
    </nav>

    <!-- Sandbox Header Badge / Notice -->
    <div style="background-color: #FAF5ED; border: 1px solid rgba(217, 174, 92, 0.4); border-left: 5px solid #D9AE5C; border-radius: 16px; padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: flex-start; gap: 1rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.03);">
        <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(217, 174, 92, 0.2); color: #B3862A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
            ⚡
        </div>
        <div>
            <h2 class="font-editorial" style="font-size: 1.15rem; font-weight: 800; color: #1E1D1B; margin: 0 0 0.25rem 0;">
                Environnement de Test / Simulation Paystack
            </h2>
            <p style="font-size: 0.84rem; color: #767067; line-height: 1.5; margin: 0;">
                Vous êtes en train de simuler un règlement via la passerelle Paystack. Aucun montant réel ne sera débité. Sélectionnez un canal de paiement pour valider la transaction test.
            </p>
        </div>
    </div>

    <!-- Carte Principale Récapitulatif & Choix Canal -->
    <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.28); border-radius: 24px; overflow: hidden; box-shadow: 0 8px 30px rgba(60, 45, 25, 0.05);">
        
        <!-- Order Recap Header -->
        <div style="background: #F8F1E5; padding: 1.75rem 2rem; border-bottom: 1px solid rgba(139, 133, 124, 0.12);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                <h1 class="font-editorial" style="font-size: 1.6rem; font-weight: 800; color: #1E1D1B; margin: 0;">
                    Récapitulatif de Commande
                </h1>
                <span style="font-size: 0.78rem; font-weight: 800; color: #D9AE5C; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.3); padding: 0.25rem 0.75rem; border-radius: 20px;">
                    Mode Démo
                </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid rgba(139, 133, 124, 0.1); font-size: 0.88rem;">
                <span style="color: #8B857C;">N° de commande</span>
                <span style="font-weight: 700; color: #1E1D1B; font-family: monospace;">#{{ $order->order_number }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid rgba(139, 133, 124, 0.1); font-size: 0.88rem;">
                <span style="color: #8B857C;">Destinataire</span>
                <span style="font-weight: 700; color: #1E1D1B;">{{ $order->customer_name ?? $order->user?->name ?? 'Client' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0; border-bottom: 1px solid rgba(139, 133, 124, 0.1); font-size: 0.88rem;">
                <span style="color: #8B857C;">Email de réception</span>
                <span style="font-weight: 600; color: #1E1D1B;">{{ $order->customer_email }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.85rem; margin-top: 0.25rem;">
                <span style="font-weight: 800; color: #1E1D1B; font-size: 1rem;">Montant total à régler</span>
                <span class="font-editorial" style="font-size: 1.8rem; font-weight: 800; color: #D9AE5C;">
                    {{ number_format($order->total_amount, 0, ',', ' ') }} <span style="font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; color: #8B857C; font-weight: 700;">FCFA</span>
                </span>
            </div>
        </div>

        <!-- Simulation Form -->
        <div style="padding: 2rem;">
            <h3 class="font-editorial" style="font-size: 1.15rem; font-weight: 800; color: #1E1D1B; margin: 0 0 1.25rem 0;">
                Choisissez le moyen de paiement à tester :
            </h3>
            
            <form action="{{ route('checkout.sandbox-confirm', $order) }}" method="POST">
                @csrf
                <input type="hidden" name="reference" value="{{ $ref }}">
                
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 2rem;">
                    
                    <!-- Wave -->
                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem 1.25rem; border: 1.5px solid rgba(217, 174, 92, 0.28); border-radius: 16px; cursor: pointer; background: #FFFDF9; transition: all 0.2s ease;">
                        <input type="radio" name="payment_channel" value="Wave" checked style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">Wave</span>
                                <div style="font-size: 0.75rem; color: #8B857C;">Paiement instantané par QR Code ou numéro</div>
                            </div>
                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(0, 178, 255, 0.12); color: #0087BF; padding: 0.25rem 0.65rem; border-radius: 12px;">Mobile Money</span>
                        </div>
                    </label>

                    <!-- Orange Money -->
                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem 1.25rem; border: 1.5px solid rgba(139, 133, 124, 0.18); border-radius: 16px; cursor: pointer; background: #FFFDF9; transition: all 0.2s ease;">
                        <input type="radio" name="payment_channel" value="Orange Money" style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">Orange Money</span>
                                <div style="font-size: 0.75rem; color: #8B857C;">Code OTP sécurisé #144#</div>
                            </div>
                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(255, 121, 0, 0.12); color: #FF7900; padding: 0.25rem 0.65rem; border-radius: 12px;">Mobile Money</span>
                        </div>
                    </label>

                    <!-- MTN Mobile Money -->
                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem 1.25rem; border: 1.5px solid rgba(139, 133, 124, 0.18); border-radius: 16px; cursor: pointer; background: #FFFDF9; transition: all 0.2s ease;">
                        <input type="radio" name="payment_channel" value="MTN Mobile Money" style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">MTN MoMo</span>
                                <div style="font-size: 0.75rem; color: #8B857C;">Validation directe sur smartphone</div>
                            </div>
                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(255, 204, 0, 0.18); color: #B38600; padding: 0.25rem 0.65rem; border-radius: 12px;">Mobile Money</span>
                        </div>
                    </label>

                    <!-- Moov Money -->
                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem 1.25rem; border: 1.5px solid rgba(139, 133, 124, 0.18); border-radius: 16px; cursor: pointer; background: #FFFDF9; transition: all 0.2s ease;">
                        <input type="radio" name="payment_channel" value="Moov Money" style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">Moov Money</span>
                                <div style="font-size: 0.75rem; color: #8B857C;">Paiement rapide via Flooz</div>
                            </div>
                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(0, 79, 159, 0.12); color: #004F9F; padding: 0.25rem 0.65rem; border-radius: 12px;">Mobile Money</span>
                        </div>
                    </label>

                    <!-- Carte Bancaire -->
                    <label style="display: flex; align-items: center; gap: 0.85rem; padding: 1rem 1.25rem; border: 1.5px solid rgba(139, 133, 124, 0.18); border-radius: 16px; cursor: pointer; background: #FFFDF9; transition: all 0.2s ease;">
                        <input type="radio" name="payment_channel" value="Carte Bancaire Visa/Mastercard" style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">Carte Visa / Mastercard</span>
                                <div style="font-size: 0.75rem; color: #8B857C;">Carte bancaire internationale ou locale</div>
                            </div>
                            <span style="font-size: 0.7rem; font-weight: 800; background: rgba(30, 29, 27, 0.08); color: #1E1D1B; padding: 0.25rem 0.65rem; border-radius: 12px;">Carte Bancaire</span>
                        </div>
                    </label>

                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <button type="submit" class="auth-btn-primary" style="padding: 1rem 1.5rem; font-size: 1rem; border-radius: 18px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Valider le paiement simulé (Succès)</span>
                    </button>
                    
                    <a href="{{ route('cart.index') }}" style="text-align: center; font-size: 0.85rem; font-weight: 600; color: #8B857C; text-decoration: underline; padding: 0.5rem;">
                        Annuler et retourner au panier
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
