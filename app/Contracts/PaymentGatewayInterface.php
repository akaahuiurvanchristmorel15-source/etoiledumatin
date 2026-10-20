<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initialise une session de paiement pour la commande.
     *
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initializePayment(Order $order, string $callbackUrl): array;

    /**
     * Vérifie le statut d'une transaction via sa référence.
     *
     * @return array{success: bool, reference: string, amount: int, message: string, raw_data: array}
     */
    public function verifyTransaction(string $reference): array;

    /**
     * Valide et traite la notification de webhook entrant.
     *
     * @return array{verified: bool, event: string, reference: ?string, data: array}
     */
    public function handleWebhook(Request $request): array;

    /**
     * Indique si le paiement simulé (sans appel à la passerelle) est autorisé.
     */
    public function isSimulationEnabled(): bool;
}
