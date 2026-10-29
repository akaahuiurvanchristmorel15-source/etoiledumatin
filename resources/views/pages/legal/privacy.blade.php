@extends('pages.legal.layout')
@section('title', 'Politique de confidentialité — NSOROMMA')
@section('meta_description', 'Quelles données NSOROMMA collecte, pourquoi, combien de temps, et comment exercer vos droits.')
@section('legal_title', 'Politique de confidentialité')

@section('legal_body')
    <p>
        Cette politique explique quelles données personnelles <x-legal-value name="legal_company_name" /> collecte lorsque vous utilisez
        la librairie NSOROMMA, pourquoi, avec qui elles sont partagées, combien de temps elles sont conservées et comment exercer vos droits.
        Elle s'applique conformément à la loi ivoirienne n° 2013-450 du 19 juin 2013 relative à la protection des données à caractère personnel.
    </p>

    <h2>1. Responsable du traitement</h2>
    <p>
        <x-legal-value name="legal_company_name" />, <x-legal-value name="legal_address" />.
        Contact pour toute question relative à vos données : <x-legal-value name="contact_email" />.
        Déclaration ARTCI : <x-legal-value name="legal_artci" />.
    </p>

    <h2>2. Données collectées et finalités</h2>
    <table>
        <thead>
            <tr><th>Données</th><th>Quand</th><th>Pourquoi</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Nom, adresse e-mail, mot de passe (chiffré), téléphone</td>
                <td>Création de compte, connexion avec Google</td>
                <td>Gérer votre compte et votre bibliothèque (livres numériques, écoutes audio)</td>
            </tr>
            <tr>
                <td>Nom, e-mail, téléphone, adresse de livraison, consignes de livraison</td>
                <td>Commande</td>
                <td>Traiter, payer, livrer la commande et vous en informer ; obligations comptables</td>
            </tr>
            <tr>
                <td>Livres achetés, abonnements audio, nombre de téléchargements</td>
                <td>Après paiement</td>
                <td>Vous donner accès à vos achats ; limiter les téléchargements abusifs</td>
            </tr>
            <tr>
                <td>Vos nom et adresse e-mail, imprimés dans les PDF achetés</td>
                <td>Téléchargement d'un e-book</td>
                <td>Filigrane nominatif protégeant les livres contre la copie illicite</td>
            </tr>
            <tr>
                <td>Nom affiché, note et commentaire</td>
                <td>Dépôt d'un avis</td>
                <td>Publication de l'avis sur la fiche du livre, après modération</td>
            </tr>
            <tr>
                <td>Adresse IP, navigateur, date et nature de certaines actions (connexion, commande, téléchargement)</td>
                <td>Utilisation du site</td>
                <td>Sécurité du site, prévention de la fraude et des abus</td>
            </tr>
        </tbody>
    </table>
    <p>Nous ne vendons pas vos données et ne les utilisons pas à des fins publicitaires. Le site n'utilise aucun outil de mesure d'audience ni de publicité.</p>

    <h2>3. Destinataires</h2>
    <p>Vos données sont accessibles à l'équipe NSOROMMA habilitée (gestion des commandes, du catalogue, de la livraison) et, pour ce qui les concerne, à :</p>
    <ul>
        <li><strong>Paystack</strong>, pour le paiement par Mobile Money ou carte bancaire. Vos numéros de carte ne transitent ni ne sont conservés sur notre site.</li>
        <li><strong>Google</strong>, si vous choisissez de vous connecter avec votre compte Google (nous recevons alors vos nom et adresse e-mail).</li>
        <li><strong>Le coursier ou transporteur</strong>, pour les livres papier : nom, téléphone et adresse de livraison.</li>
        <li><strong>Notre hébergeur</strong> (<x-legal-value name="legal_host" />), qui stocke techniquement le site et sa base de données.</li>
        <li><strong>Notre prestataire d'envoi d'e-mails</strong>, pour la confirmation de votre adresse et la réinitialisation du mot de passe.</li>
    </ul>
    <p>
        Les voix de synthèse des livres audio sont produites par OpenAI à partir du seul texte des livres : aucune donnée personnelle ne lui est transmise.
        Certains prestataires étant établis hors de Côte d'Ivoire (notamment aux États-Unis), vos données peuvent y être transférées et y être hébergées.
    </p>

    <h2>4. Durées de conservation</h2>
    <ul>
        <li>Compte : tant qu'il est actif, puis suppression sur demande.</li>
        <li>Commandes et factures : 10 ans, durée imposée par les obligations comptables.</li>
        <li>Journal de sécurité (adresse IP, navigateur) : 12 mois, puis suppression automatique.</li>
        <li>Avis : tant que le livre est au catalogue, ou jusqu'à votre demande de retrait.</li>
    </ul>

    <h2>5. Cookies et stockage local</h2>
    <p>Le site n'utilise que des cookies indispensables à son fonctionnement, qui ne nécessitent pas votre consentement :</p>
    <ul>
        <li>cookie de session, qui garde votre panier et votre connexion (supprimé après {{ config('session.lifetime') }} minutes d'inactivité) ;</li>
        <li>cookie de sécurité « XSRF-TOKEN », qui protège les formulaires contre les envois frauduleux ;</li>
        <li>cookie « se souvenir de moi », seulement si vous cochez cette option à la connexion.</li>
    </ul>
    <p>Votre navigateur garde aussi en mémoire le fait que vous avez fermé le bandeau d'annonce, pour ne pas le réafficher.</p>

    <h2>6. Sécurité</h2>
    <p>
        Les échanges sont chiffrés (HTTPS), les mots de passe sont stockés sous forme chiffrée, les fichiers achetés ne sont accessibles
        qu'au moyen de liens personnels à durée limitée et l'accès à l'administration est réservé au personnel habilité.
    </p>

    <h2>7. Vos droits</h2>
    <p>Conformément à la loi n° 2013-450, vous disposez des droits suivants sur vos données :</p>
    <ul>
        <li>droit d'accès et d'obtenir une copie de vos données ;</li>
        <li>droit de rectification des données inexactes (vos informations de compte sont aussi modifiables à tout moment) ;</li>
        <li>droit d'opposition et de suppression, sous réserve des données que la loi nous oblige à conserver ;</li>
        <li>droit de saisir l'ARTCI si vous estimez que vos droits ne sont pas respectés.</li>
    </ul>
    <p>Pour les exercer, écrivez-nous à <x-legal-value name="contact_email" /> en précisant l'adresse e-mail de votre compte. Nous répondons dans un délai d'un mois.</p>

    <h2>8. Mineurs</h2>
    <p>La création d'un compte et les achats sont réservés aux personnes majeures ou agissant avec l'accord de leur représentant légal.</p>

    <h2>9. Modifications</h2>
    <p>Cette politique peut évoluer ; la date de dernière mise à jour figure en haut de la page.</p>
@endsection
