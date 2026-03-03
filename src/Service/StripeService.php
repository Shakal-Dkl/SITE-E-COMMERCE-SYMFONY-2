<?php

namespace App\Service;

use Stripe\StripeClient;

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
        if ($this->isSimulationMode()) {
            return [
                'checkoutUrl' => $successUrl.'?simulated=1',
                'sessionId' => null,
                'simulated' => true,
            ];
        }

        $stripe = new StripeClient($this->stripeSecretKey);
        $lineItems = [];

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
        return $this->stripeSecretKey === '' || str_contains($this->stripeSecretKey, '***');
    }
}
