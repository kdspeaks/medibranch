<?php

namespace App\Services;

use App\Models\Tax;

class PricingService
{
    public function lineWithTax(int $quantity, float $unitPrice, ?int $taxId, bool $isTaxInclusive = false): array
    {
        $taxRate = 0.0;

        if ($taxId) {
            $tax = Tax::find($taxId);

            if ($tax?->is_active) {
                $taxRate = (float) $tax->rate;
            }
        }

        return $this->lineWithTaxRate($quantity, $unitPrice, $taxRate, $isTaxInclusive);
    }

    public function lineWithTaxRate(int $quantity, float $unitPrice, float $taxRate, bool $isTaxInclusive = false): array
    {
        $line = $quantity * $unitPrice;
        
        if ($isTaxInclusive) {
            $taxAmount = $taxRate > 0 ? $line - ($line / (1 + ($taxRate / 100))) : 0.0;
            $subTotal = $line - $taxAmount;
            $lineTotalAmount = $line;
        } else {
            $taxAmount = $taxRate > 0 ? $line * ($taxRate / 100) : 0.0;
            $subTotal = $line;
            $lineTotalAmount = $line + $taxAmount;
        }

        return [
            'line_sub_total' => $this->money($subTotal),
            'line_total_amount' => $this->money($lineTotalAmount),
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
