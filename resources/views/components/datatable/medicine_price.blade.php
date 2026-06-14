@php
    $sale_price = $getRecord()->sale_price;
    $purchase_price = $getRecord()->purchase_price;
@endphp
<div class="flex-col items-center px-3 whitespace-nowrap">
    <div class="text-xs text-text dark:text-text-dark">
        <span class="text-xxs text-text-muted dark:text-text-muted-dark">Selling:</span> &#8377;{{ $sale_price }}
    </div>
    <div class="text-xs text-text dark:text-text-dark">
        <span class="text-xxs text-text-muted dark:text-text-muted-dark">Purchase:</span> &#8377;{{ $purchase_price }}
    </div>
</div>
