<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }
        .receipt-container {
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mt-2 { margin-top: 10px; }
        .border-b { border-bottom: 1px dashed #000; }
        .border-t { border-top: 1px dashed #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 0; text-align: left; vertical-align: top; }
        .item-name { max-width: 45mm; word-wrap: break-word; }
        @media print {
            body { margin: 0; padding: 0; }
            .receipt-container { width: 100%; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center mb-3">
            <div class="font-bold" style="font-size: 16px;">{{ $sale->branch->name ?? config('app.name') }}</div>
            <div>{{ $sale->branch->address ?? '' }}</div>
            @if($sale->branch?->phone)
            <div>Tel: {{ $sale->branch->phone }}</div>
            @endif
            @if($sale->branch?->gst_number)
            <div>GSTIN: {{ $sale->branch->gst_number }}</div>
            @endif
            @if($sale->branch?->drug_license_number)
            <div>DL No: {{ $sale->branch->drug_license_number }}</div>
            @endif
        </div>

        <!-- Sale Details -->
        <div class="mb-2 border-b" style="padding-bottom: 5px;">
            @if($isEstimate ?? false)
            <div class="text-center font-bold mb-2">ESTIMATE / QUOTATION</div>
            <div>Date: {{ $sale->created_at->format('d/m/Y h:i A') }}</div>
            @else
            <div>Inv: {{ $sale->invoice_number }}</div>
            <div>Date: {{ $sale->created_at->format('d/m/Y h:i A') }}</div>
            @endif
            <div>Cashier: {{ $sale->user->name ?? 'System' }}</div>
            @if($sale->customer)
            <div>Customer: {{ $sale->customer->name }} ({{ $sale->customer->phone }})</div>
            @endif
        </div>

        <!-- Items Table -->
        <table class="mb-2">
            <thead>
                <tr class="border-b">
                    <th style="width: 12px;">#</th>
                    <th class="item-name">Item</th>
                    <th class="text-right">MRP</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td style="vertical-align: top;">{{ $index + 1 }}</td>
                    <td class="item-name">
                        {{ $item->medicine->name }}<br>
                        @if($item->medicine->manufacturer)
                        <small style="font-style: italic;">Brand: {{ $item->medicine->manufacturer->name }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ currency() }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ (int)$item->quantity }}</td>
                    <td class="text-right">{{ currency() }}{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="border-t font-bold">
                    <td colspan="3" class="text-right" style="padding-top: 4px;">Total</td>
                    <td class="text-center" style="padding-top: 4px;">{{ (int)$sale->items->sum('quantity') }}</td>
                    <td class="text-right" style="padding-top: 4px;">{{ currency() }}{{ number_format($sale->items->sum('total_amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="border-t pt-2 mb-3">
            <table style="width: 100%;">
                <tr>
                    <td>Sub Total:</td>
                    <td class="text-right">{{ currency() }}{{ number_format($sale->sub_total, 2) }}</td>
                </tr>
                @if($sale->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-{{ currency() }}{{ number_format($sale->discount, 2) }}</td>
                </tr>
                @endif
                @if(($sale->branch->taxable ?? false) && $sale->tax_amount > 0)
                <tr>
                    <td>CGST:</td>
                    <td class="text-right">{{ currency() }}{{ number_format($sale->tax_amount / 2, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST:</td>
                    <td class="text-right">{{ currency() }}{{ number_format($sale->tax_amount / 2, 2) }}</td>
                </tr>
                @endif
                @if($sale->round_off != 0)
                <tr>
                    <td>Round Off:</td>
                    <td class="text-right">
                        @if($sale->round_off < 0) - @endif
                        {{ currency() }}{{ number_format(abs($sale->round_off), 2) }}
                    </td>
                </tr>
                @endif
                <tr class="font-bold" style="font-size: 14px;">
                    <td style="padding-top: 5px;">Grand Total:</td>
                    <td class="text-right" style="padding-top: 5px;">{{ currency() }}{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- GST Summary -->
        @php
            $gstSummary = [];
            foreach ($sale->items as $item) {
                $rate = (float) ($item->medicine->tax->rate ?? 0);
                if ($rate > 0) {
                    $key = (string) $rate;
                    if (!isset($gstSummary[$key])) {
                        $gstSummary[$key] = ['taxable_amount' => 0, 'tax_amount' => 0];
                    }
                    $gstSummary[$key]['taxable_amount'] += $item->sub_total;
                    $gstSummary[$key]['tax_amount'] += $item->tax_amount;
                }
            }
        @endphp
        @if(($sale->branch->taxable ?? false) && count($gstSummary) > 0)
        <div class="border-t pt-2 mb-2">
            <div class="text-center font-bold mb-1">GST</div>
            <table>
                <thead>
                    <tr class="border-b">
                        <th>Taxable Amt</th>
                        <th class="text-right">CGST %</th>
                        <th class="text-right">CGST Amt</th>
                        <th class="text-right">SGST %</th>
                        <th class="text-right">SGST Amt</th>
                        <th class="text-right">Tax Amt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gstSummary as $rate => $data)
                    <tr>
                        <td>{{ number_format($data['taxable_amount'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$rate / 2, 2) }}%</td>
                        <td class="text-right">{{ number_format($data['tax_amount'] / 2, 2) }}</td>
                        <td class="text-right">{{ number_format((float)$rate / 2, 2) }}%</td>
                        <td class="text-right">{{ number_format($data['tax_amount'] / 2, 2) }}</td>
                        <td class="text-right">{{ number_format($data['tax_amount'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Payment Details -->
        @if(!($isEstimate ?? false))
        <div class="mb-3">
            <div>Paid via: {{ strtoupper($sale->payment_method) }}</div>
            @if($sale->payment_reference)
            <div>Ref: {{ $sale->payment_reference }}</div>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center border-t" style="padding-top: 10px;">
            <div class="font-bold mb-1">Thank You!</div>
            <div>Please visit again</div>
        </div>
    </div>
</body>
</html>
