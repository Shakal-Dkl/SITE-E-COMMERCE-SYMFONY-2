<?php

namespace App\Service;

use Stripe\StripeClient;

// Service d'intégration Stripe.
// Il propose un mode simulation pratique en local et un mode réel en test/production.
class StripeService
{
    public function __construct(private readonly string $stripeSecretKey)
    {
    }

    /**
     * @param array<string, array<string, mixed>> $items
     *
     * @return array{checkoutUrl: string, sessionId: string|null, simulated: bool}
     */
    public function startCheckout(array $items, string $successUrl, string $cancelUrl): array
    {
        // En démo, on évite un appel réseau Stripe et on simule un retour succès.
        if ($this->isSimulationMode()) {
            return [
                'checkoutUrl' => $successUrl.'?simulated=1',
                'sessionId' => null,
                'simulated' => true,
            ];
        }

        $stripe = new StripeClient($this->stripeSecretKey);
        $lineItems = [];

        // Construction des lignes attendues par l'API Stripe Checkout.
        foreach ($items as $item) {
            $product = $item['product'];

            $lineItems[] = [
                'quantity' => $item['quantity'],
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round(((float) $product->getPrice()) * 100),
                    'product_data' => [
                        'name' => $product->getName().' - Taille '.$item['size'],
                    ],
                ],
            ];
        }

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return [
            'checkoutUrl' => $session->url,
            'sessionId' => $session->id,
            'simulated' => false,
        ];
    }

    private function isSimulationMode(): bool
    {
        // Convention projet: clé vide ou "***" = mode simulation.
        return $this->stripeSecretKey === '' || str_contains($this->stripeSecretKey, '***');
    }
}
