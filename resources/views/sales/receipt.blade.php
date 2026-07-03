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
                    <th class="item-name">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td class="item-name">
                        {{ $item->medicine->name }}<br>
                        <small>@ {{ currency() }}{{ number_format($item->unit_price, 2) }}</small>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ currency() }}{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="border-t pt-2 mb-3">
            <table style="width: 100%;">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">{{ currency() }}{{ number_format($sale->sub_total, 2) }}</td>
                </tr>
                @if($sale->tax_amount > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">{{ currency() }}{{ number_format($sale->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($sale->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-{{ currency() }}{{ number_format($sale->discount, 2) }}</td>
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
                    <td style="padding-top: 5px;">Total:</td>
                    <td class="text-right" style="padding-top: 5px;">{{ currency() }}{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

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
