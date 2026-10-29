@extends('pages.legal.layout')
@section('title', 'Mentions légales — NSOROMMA')
@section('meta_description', 'Éditeur, hébergeur et informations légales de la librairie en ligne NSOROMMA.')
@section('legal_title', 'Mentions légales')

@section('legal_body')
    <h2>Éditeur du site</h2>
    <p>
        Le site {{ parse_url(config('app.url'), PHP_URL_HOST) }} est édité par <strong><x-legal-value name="legal_company_name" /></strong>,
        <x-legal-value name="legal_form" />.
    </p>
    <ul>
        <li>Siège social : <x-legal-value name="legal_address" /></li>
        <li>RCCM : <x-legal-value name="legal_rccm" /></li>
        <li>N° de compte contribuable : <x-legal-value name="legal_tax_id" /></li>
        <li>E-mail : <x-legal-value name="contact_email" /></li>
        <li>Téléphone : <x-legal-value name="contact_phone" /></li>
    </ul>
    <p>Directeur de la publication : <x-legal-value name="legal_publication_director" />.</p>

    <h2>Hébergement</h2>
    <p><x-legal-value name="legal_host" /></p>

    <h2>Données personnelles</h2>
    <p>
        Le traitement des données personnelles des lecteurs a fait l'objet d'une déclaration auprès de l'Autorité de Régulation
        des Télécommunications/TIC de Côte d'Ivoire (ARTCI), conformément à la loi n° 2013-450 du 19 juin 2013 :
        récépissé <x-legal-value name="legal_artci" />.
        Le détail des données collectées et de vos droits figure dans la <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.
    </p>

    <h2>Propriété intellectuelle</h2>
    <p>
        Les textes, couvertures, extraits, livres numériques, livres audio, logos et la charte graphique du site sont protégés par le droit d'auteur.
        Toute reproduction, diffusion ou revente, totale ou partielle, sans autorisation écrite de l'éditeur ou des ayants droit est interdite.
        Les livres numériques vendus portent un filigrane nominatif identifiant leur acheteur.
    </p>

    <h2>Responsabilité</h2>
    <p>
        L'éditeur s'efforce d'assurer l'exactitude des informations publiées, sans pouvoir garantir l'absence d'erreur ou d'interruption du service.
        Les liens vers des sites tiers (réseaux sociaux, WhatsApp, Paystack…) n'engagent pas la responsabilité de l'éditeur quant à leur contenu.
    </p>

    <h2>Droit applicable</h2>
    <p>Le présent site et ses mentions sont régis par le droit ivoirien.</p>
@endsection
