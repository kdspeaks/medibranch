@php
    $name = $getRecord()->name;
    $sku = $getRecord()->sku;
@endphp
<div class="flex-col items-center px-3 whitespace-nowrap">
    <div class="text-sm font-semibold text-text dark:text-text-dark">{{ $name }}</div>
    @if($sku)
        <div class="text-xs text-text-muted dark:text-text-muted-dark">{{ $sku }}</div>
    @endif
</div>