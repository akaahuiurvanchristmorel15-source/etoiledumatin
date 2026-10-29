@extends('pages.legal.layout')
@section('title', 'Conditions générales de vente — NSOROMMA')
@section('meta_description', 'Conditions de vente des livres papier, e-books PDF et abonnements d\'écoute audio de la librairie NSOROMMA.')
@section('legal_title', 'Conditions générales de vente')

@section('legal_body')
    <h2>1. Objet</h2>
    <p>
        Les présentes conditions régissent les ventes conclues sur la librairie en ligne NSOROMMA, exploitée par
        <x-legal-value name="legal_company_name" /> (RCCM <x-legal-value name="legal_rccm" />), entre l'éditeur et toute personne
        passant commande (« le client »). Passer commande vaut acceptation des présentes conditions.
    </p>

    <h2>2. Produits</h2>
    <ul>
        <li><strong>Livres papier</strong>, expédiés dans la limite des stocks indiqués sur chaque fiche.</li>
        <li><strong>E-books PDF</strong>, téléchargeables depuis « Ma bibliothèque ». Chaque exemplaire porte un filigrane au nom de l'acheteur et peut être téléchargé jusqu'à 15 fois.</li>
        <li><strong>Abonnements d'écoute audio</strong>, donnant accès en streaming à une partie d'un livre, dans les langues disponibles, pour la durée choisie (1 semaine, 1 mois, 3 mois ou 1 an). L'écoute se fait en ligne depuis « Ma bibliothèque » et nécessite un compte ; les fichiers audio ne sont pas téléchargeables.</li>
    </ul>
    <p>Les livres numériques et les écoutes sont destinés à un usage strictement personnel : leur copie, revente ou diffusion est interdite.</p>

    <h2>3. Prix</h2>
    <p>
        Les prix sont indiqués en francs CFA (FCFA / XOF), toutes taxes comprises. Les frais de livraison des livres papier s'ajoutent
        au prix et sont affichés avant le paiement. Le prix applicable est celui affiché au moment de la commande.
    </p>

    <h2>4. Commande et paiement</h2>
    <p>
        Le paiement s'effectue en ligne par l'intermédiaire de Paystack, par Mobile Money ou carte bancaire. La commande est ferme dès la
        confirmation du paiement ; un récapitulatif est alors affiché et la commande peut être suivie avec son numéro.
        En cas d'échec ou d'annulation du paiement, la commande n'est pas validée.
    </p>

    <h2>5. Livraison des livres papier</h2>
    <table>
        <thead>
            <tr><th>Zone</th><th>Délai indicatif</th><th>Frais</th></tr>
        </thead>
        <tbody>
            @foreach(config('shipping.zones') as $zone)
                <tr>
                    <td>{{ $zone['name'] }}</td>
                    <td>{{ $zone['delay'] }}</td>
                    <td>{{ number_format($zone['price'], 0, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>
        Les délais courent à compter de la confirmation du paiement et sont donnés à titre indicatif. Le client vérifie l'état du colis à
        réception et signale tout livre manquant ou endommagé dans les 48 heures, photos à l'appui, à <x-legal-value name="contact_email" />.
    </p>

    <h2>6. Accès aux contenus numériques</h2>
    <p>
        Les e-books et les écoutes audio sont disponibles dans « Ma bibliothèque » dès la confirmation du paiement.
        Un abonnement audio commence à la confirmation du paiement ; s'il est souscrit avant la fin d'un abonnement en cours à la même partie,
        il prolonge cet abonnement. À l'échéance, l'écoute s'arrête jusqu'à un nouvel abonnement.
    </p>

    <h2>7. Rétractation, retours et remboursements</h2>
    <ul>
        <li>
            <strong>Livres papier</strong> : un livre défectueux, endommagé ou non conforme à la commande est échangé ou remboursé, après
            signalement dans les 48 heures suivant la réception et retour du livre dans son état d'origine.
        </li>
        <li>
            <strong>E-books et écoutes audio</strong> : s'agissant de contenus numériques fournis immédiatement après le paiement, ils ne
            peuvent être ni retournés ni remboursés une fois le téléchargement ou l'écoute commencé, sauf défaut technique empêchant leur utilisation.
        </li>
    </ul>
    <p>Les remboursements acceptés sont effectués sur le moyen de paiement utilisé lors de la commande.</p>

    <h2>8. Service client et réclamations</h2>
    <p>
        Pour toute question ou réclamation : <x-legal-value name="contact_email" />, <x-legal-value name="contact_phone" />
        @if(\App\Models\Setting::content('support_whatsapp') !== '')
            ou <a href="https://wa.me/{{ \App\Models\Setting::content('support_whatsapp') }}">WhatsApp</a>
        @endif.
        Merci d'indiquer votre numéro de commande.
    </p>

    <h2>9. Données personnelles</h2>
    <p>Les données nécessaires à la commande sont traitées conformément à la <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.</p>

    <h2>10. Droit applicable et litiges</h2>
    <p>
        Les présentes conditions sont soumises au droit ivoirien, notamment à la loi n° 2013-546 du 30 juillet 2013 relative aux transactions
        électroniques. En cas de litige, une solution amiable sera recherchée avant toute action devant les juridictions compétentes d'Abidjan.
    </p>
@endsection
