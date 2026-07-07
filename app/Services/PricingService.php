<?php

namespace App\Services;

use App\Models\Tax;

class PricingService
{
    public function lineWithTax(int $quantity, float $unitPrice, ?int $taxId): array
    {
        $taxRate = 0.0;

        if ($taxId) {
            $tax = Tax::find($taxId);

            if ($tax?->is_active) {
                $taxRate = (float) $tax->rate;
            }
        }

        return $this->lineWithTaxRate($quantity, $unitPrice, $taxRate);
    }

    public function lineWithTaxRate(int $quantity, float $unitPrice, float $taxRate): array
    {
        $line = $quantity * $unitPrice;
        $taxAmount = $taxRate > 0 ? $line * ($taxRate / 100) : 0.0;

        return [
            'line_total_amount' => $this->money($line + $taxAmount),
            'tax_amount' => $this->money($taxAmount),
            'tax_rate' => $this->money($taxRate),
        ];
    }

    public function totalFromItems(array $items): float
    {
        $paise = collect($items)->reduce(function (int $carry, array $item): int {
            $line = (float) ($item['line_total_amount'] ?? 0);

            return $carry + (int) round($line * 100);
        }, 0);

        return $this->money($paise / 100);
    }

    public function purchasePriceFromMrp(float $mrp, float $discountOnPurchase): float
    {
        return $this->money($mrp - ($mrp * ($discountOnPurchase / 100)));
    }

    public function money(float $amount): float
    {
        return round($amount, 2);
    }
}
