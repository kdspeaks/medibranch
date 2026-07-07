@php
    $mrp = $getRecord()->mrp;
    $purchase_price = $getRecord()->purchase_price;
@endphp
<div class="flex-col items-center px-3 whitespace-nowrap">
    <div class="text-xs text-text dark:text-text-dark">
        <span class="text-xxs text-text-muted dark:text-text-muted-dark">MRP:</span> &#8377;{{ $mrp }}
    </div>
    <div class="text-xs text-text dark:text-text-dark">
        <span class="text-xxs text-text-muted dark:text-text-muted-dark">Purchase:</span> &#8377;{{ $purchase_price }}
    </div>
</div>
