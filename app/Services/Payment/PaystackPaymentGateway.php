<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackPaymentGateway implements PaymentGatewayInterface
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $paymentUrl;

    protected bool $sandboxMode;

    public function __construct()
    {
        $this->secretKey = (string) config('paystack.secret_key');
        $this->publicKey = (string) config('paystack.public_key');
        $this->paymentUrl = rtrim((string) config('paystack.payment_url', 'https://api.paystack.co'), '/');
        $this->sandboxMode = (bool) config('paystack.sandbox_mode', true);
    }

    /**
     * Initialise la transaction auprès de Paystack ou lance la simulation sandbox.
     */
    public function initializePayment(Order $order, string $callbackUrl): array
    {
        $reference = 'PAY_'.strtoupper(uniqid()).'_'.$order->id;

        // Mode simulation (développement uniquement) : interface de paiement factice
        if ($this->isSimulationEnabled()) {
            return [
                'authorization_url' => route('checkout.sandbox-payment', $order),
                'access_code' => 'sandbox_access_'.uniqid(),
                'reference' => $reference,
            ];
        }

        try {
            // Le XOF n'a pas de sous-unité, mais Paystack exige tout de même le montant multiplié par 100
            $payload = [
                'email' => $order->customer_email,
                'amount' => (int) $order->total_amount * 100,
                'currency' => config('paystack.currency', 'XOF'),
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'channels' => $this->channelsFor($order),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'custom_fields' => [
                        [
                            'display_name' => 'Numéro de Commande',
                            'variable_name' => 'order_number',
                            'value' => $order->order_number,
                        ],
                    ],
                ],
            ];

            $response = $this->client()
                ->timeout(15)
                ->post("{$this->paymentUrl}/transaction/initialize", $payload);

            if ($response->successful() && $response->json('status') === true) {
                $data = $response->json('data');

                return [
                    'authorization_url' => $data['authorization_url'],
                    'access_code' => $data['access_code'],
                    'reference' => $reference,
                ];
            }

            Log::error('Paystack Initialization Error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new \RuntimeException('Erreur lors de l\'initialisation du paiement Paystack : '.($response->json('message') ?? 'Échec de connexion'));
        } catch (\Exception $e) {
            Log::error('Paystack Exception', ['message' => $e->getMessage()]);

            throw $e;
        }
    }

    /**
     * Vérifie la transaction auprès de Paystack.
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            $response = $this->client()
                ->timeout(15)
                ->get("{$this->paymentUrl}/transaction/verify/{$reference}");

            if ($response->successful() && $response->json('status') === true) {
                $data = $response->json('data');
                $isSuccessful = ($data['status'] ?? null) === 'success' && $this->isExpectedCurrency($data['currency'] ?? null);

                return [
                    'success' => $isSuccessful,
                    'reference' => $reference,
                    'amount' => (int) (($data['amount'] ?? 0) / 100),
                    'message' => $data['gateway_response'] ?? 'Transaction traitée',
                    'raw_data' => $data,
                ];
            }

            return [
                'success' => false,
                'reference' => $reference,
                'amount' => 0,
                'message' => $response->json('message') ?? 'Échec de vérification',
                'raw_data' => $response->json() ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Verify Exception', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'reference' => $reference,
                'amount' => 0,
                'message' => $e->getMessage(),
                'raw_data' => [],
            ];
        }
    }

    /**
     * Valide le Webhook HMAC SHA-512 envoyé par Paystack.
     */
    public function handleWebhook(Request $request): array
    {
        $signature = $request->header('x-paystack-signature');
        $rawPayload = $request->getContent();

        // En mode simulation (développement uniquement), la signature n'est pas exigée
        if ($this->isSimulationEnabled()) {
            return [
                'verified' => true,
                'event' => $request->input('event', 'charge.success'),
                'reference' => $request->input('data.reference'),
                'data' => $request->input('data', []),
            ];
        }

        $expectedSignature = hash_hmac('sha512', $rawPayload, $this->secretKey);

        if (! hash_equals($expectedSignature, (string) $signature)) {
            Log::warning('Paystack Webhook Signature Invalide', [
                'received' => $signature,
            ]);

            return [
                'verified' => false,
                'event' => 'invalid_signature',
                'reference' => null,
                'data' => [],
            ];
        }

        $payload = json_decode($rawPayload, true) ?: [];

        return [
            'verified' => true,
            'event' => $payload['event'] ?? 'unknown',
            'reference' => $payload['data']['reference'] ?? null,
            'data' => $payload['data'] ?? [],
        ];
    }

    /**
     * Client HTTP authentifié auprès de l'API Paystack.
     */
    protected function client(): PendingRequest
    {
        $client = Http::withToken($this->secretKey);

        if ($caBundle = config('paystack.ca_bundle')) {
            $client->withOptions(['verify' => $caBundle]);
        }

        return $client;
    }

    /**
     * Le moyen choisi au moment de commander détermine ce que la page Paystack propose.
     *
     * @return list<string>
     */
    protected function channelsFor(Order $order): array
    {
        return match ($order->payment_method) {
            'paystack_momo' => ['mobile_money'],
            'paystack_card' => ['card'],
            default => ['mobile_money', 'card'],
        };
    }

    /**
     * Un paiement encaissé dans une autre devise que celle de la boutique n'est jamais accepté.
     */
    protected function isExpectedCurrency(?string $currency): bool
    {
        return strtoupper((string) $currency) === strtoupper((string) config('paystack.currency', 'XOF'));
    }

    /**
     * La simulation n'est possible qu'en mode sandbox, sans vraie clé, et jamais en production.
     */
    public function isSimulationEnabled(): bool
    {
        return $this->sandboxMode && $this->isSampleKey() && ! app()->isProduction();
    }

    protected function isSampleKey(): bool
    {
        return str_contains($this->secretKey, 'sample') || str_contains($this->secretKey, 'placeholder') || empty($this->secretKey);
    }
}
