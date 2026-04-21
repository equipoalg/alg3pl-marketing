<?php

namespace App\Services\Quote;

use App\Models\Lead;
use App\Models\Country;
use Carbon\Carbon;

class QuoteGeneratorService
{
    private array $serviceRates = [
        'ocean_freight' => ['base' => 1200, 'per_cbm' => 45],
        'air_freight' => ['base' => 800, 'per_kg' => 3.5],
        'customs' => ['base' => 350, 'percentage' => 0.015],
        'warehousing' => ['base' => 200, 'per_pallet_month' => 25],
        'ground_transport' => ['base' => 150, 'per_km' => 1.8],
        'last_mile' => ['base' => 80, 'per_delivery' => 12],
    ];

    private array $countryMultipliers = [
        'sv' => 1.0,
        'gt' => 1.05,
        'hn' => 0.95,
        'ni' => 0.90,
        'cr' => 1.15,
        'pa' => 1.20,
        'us' => 1.50,
    ];

    /**
     * Generate a quote for a lead based on their interests.
     */
    public function generateForLead(Lead $lead): array
    {
        $services = $this->inferServices($lead);
        $country = $lead->country;
        $multiplier = $this->countryMultipliers[$country?->code ?? 'sv'] ?? 1.0;

        $lineItems = [];
        $total = 0;

        foreach ($services as $service) {
            $rate = $this->serviceRates[$service] ?? null;
            if (!$rate) continue;

            $amount = $rate['base'] * $multiplier;
            $lineItems[] = [
                'service' => $this->serviceName($service),
                'description' => $this->serviceDescription($service),
                'base_rate' => $rate['base'],
                'multiplier' => $multiplier,
                'amount' => round($amount, 2),
            ];
            $total += $amount;
        }

        return [
            'quote_number' => 'ALG-' . strtoupper($country?->code ?? 'XX') . '-' . now()->format('Ymd') . '-' . $lead->id,
            'date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(30)->format('Y-m-d'),
            'lead' => [
                'name' => $lead->name,
                'company' => $lead->company,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'country' => $country?->name,
            ],
            'line_items' => $lineItems,
            'subtotal' => round($total, 2),
            'tax_rate' => $this->getTaxRate($country?->code),
            'tax_amount' => round($total * $this->getTaxRate($country?->code), 2),
            'total' => round($total * (1 + $this->getTaxRate($country?->code)), 2),
            'currency' => 'USD',
            'terms' => $this->getTerms($country?->code),
            'notes' => "Quote generated automatically based on lead interest: {$lead->service_interest}",
        ];
    }

    /**
     * Generate HTML for the quote (PDF-ready).
     */
    public function generateHtml(Lead $lead): string
    {
        $quote = $this->generateForLead($lead);

        $itemsHtml = '';
        foreach ($quote['line_items'] as $i => $item) {
            $n = $i + 1;
            $itemsHtml .= "<tr>
                <td style='padding:8px;border-bottom:1px solid #e5e7eb'>{$n}</td>
                <td style='padding:8px;border-bottom:1px solid #e5e7eb'><strong>{$item['service']}</strong><br><small style='color:#6b7280'>{$item['description']}</small></td>
                <td style='padding:8px;border-bottom:1px solid #e5e7eb;text-align:right'>\${$item['amount']}</td>
            </tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Quote {$quote['quote_number']}</title></head>
<body style="font-family:Inter,Arial,sans-serif;color:#1f2937;max-width:800px;margin:0 auto;padding:40px">
    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:40px">
        <div>
            <h1 style="color:#1e40af;margin:0;font-size:28px">ALG3PL</h1>
            <p style="color:#6b7280;margin:4px 0">Logistics Solutions</p>
        </div>
        <div style="text-align:right">
            <h2 style="margin:0;font-size:20px">COTIZACIÓN</h2>
            <p style="color:#6b7280;margin:4px 0">{$quote['quote_number']}</p>
            <p style="color:#6b7280;margin:4px 0">Fecha: {$quote['date']}</p>
            <p style="color:#6b7280;margin:4px 0">Válida hasta: {$quote['valid_until']}</p>
        </div>
    </div>

    <div style="background:#f9fafb;padding:20px;border-radius:8px;margin-bottom:30px">
        <h3 style="margin:0 0 10px">Cliente</h3>
        <p style="margin:2px 0"><strong>{$quote['lead']['name']}</strong></p>
        <p style="margin:2px 0">{$quote['lead']['company']}</p>
        <p style="margin:2px 0">{$quote['lead']['email']} | {$quote['lead']['phone']}</p>
        <p style="margin:2px 0">{$quote['lead']['country']}</p>
    </div>

    <table style="width:100%;border-collapse:collapse;margin-bottom:30px">
        <thead>
            <tr style="background:#1e40af;color:white">
                <th style="padding:10px;text-align:left;width:40px">#</th>
                <th style="padding:10px;text-align:left">Servicio</th>
                <th style="padding:10px;text-align:right;width:120px">Monto</th>
            </tr>
        </thead>
        <tbody>{$itemsHtml}</tbody>
        <tfoot>
            <tr><td colspan="2" style="padding:8px;text-align:right"><strong>Subtotal</strong></td><td style="padding:8px;text-align:right">\${$quote['subtotal']}</td></tr>
            <tr><td colspan="2" style="padding:8px;text-align:right">IVA ({$quote['tax_rate']}%)</td><td style="padding:8px;text-align:right">\${$quote['tax_amount']}</td></tr>
            <tr style="background:#f0f9ff"><td colspan="2" style="padding:12px;text-align:right;font-size:18px"><strong>Total</strong></td><td style="padding:12px;text-align:right;font-size:18px"><strong>\${$quote['total']} USD</strong></td></tr>
        </tfoot>
    </table>

    <div style="background:#fffbeb;padding:15px;border-radius:8px;border-left:4px solid #f59e0b;margin-bottom:20px">
        <strong>Términos:</strong> {$quote['terms']}
    </div>

    <p style="color:#6b7280;font-size:12px;text-align:center;margin-top:40px">
        {$quote['notes']}<br>
        ALG3PL — marketing.alg3pl.com
    </p>
</body>
</html>
HTML;
    }

    private function inferServices(Lead $lead): array
    {
        $interest = strtolower($lead->service_interest ?? '');
        $services = [];

        if (str_contains($interest, 'ocean') || str_contains($interest, 'marítim') || str_contains($interest, 'maritim')) {
            $services[] = 'ocean_freight';
        }
        if (str_contains($interest, 'aére') || str_contains($interest, 'aereo') || str_contains($interest, 'air')) {
            $services[] = 'air_freight';
        }
        if (str_contains($interest, 'aduana') || str_contains($interest, 'custom')) {
            $services[] = 'customs';
        }
        if (str_contains($interest, 'almacen') || str_contains($interest, 'warehouse') || str_contains($interest, 'bodega')) {
            $services[] = 'warehousing';
        }
        if (str_contains($interest, 'terrestre') || str_contains($interest, 'ground') || str_contains($interest, 'transport')) {
            $services[] = 'ground_transport';
        }
        if (str_contains($interest, 'última milla') || str_contains($interest, 'last mile') || str_contains($interest, 'entrega')) {
            $services[] = 'last_mile';
        }

        // Default: ocean + customs if nothing specific
        if (empty($services)) {
            $services = ['ocean_freight', 'customs'];
        }

        return $services;
    }

    private function serviceName(string $key): string
    {
        return match ($key) {
            'ocean_freight' => 'Flete Marítimo',
            'air_freight' => 'Flete Aéreo',
            'customs' => 'Gestión Aduanera',
            'warehousing' => 'Almacenaje',
            'ground_transport' => 'Transporte Terrestre',
            'last_mile' => 'Última Milla',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    private function serviceDescription(string $key): string
    {
        return match ($key) {
            'ocean_freight' => 'Consolidación y transporte marítimo FCL/LCL',
            'air_freight' => 'Carga aérea express y estándar',
            'customs' => 'Tramitología aduanera, clasificación arancelaria',
            'warehousing' => 'Almacenaje en bodega fiscalizada',
            'ground_transport' => 'Transporte terrestre puerta a puerta',
            'last_mile' => 'Distribución y entrega final',
            default => '',
        };
    }

    private function getTaxRate(?string $countryCode): float
    {
        return match ($countryCode) {
            'sv' => 0.13,
            'gt' => 0.12,
            'hn' => 0.15,
            'ni' => 0.15,
            'cr' => 0.13,
            'pa' => 0.07,
            'us' => 0.0,
            default => 0.13,
        };
    }

    private function getTerms(?string $countryCode): string
    {
        return 'Pago 50% anticipo, 50% contra entrega de documentos. Precios sujetos a variación de navieras. Cotización válida por 30 días.';
    }
}
