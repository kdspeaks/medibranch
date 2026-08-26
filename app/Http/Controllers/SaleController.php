<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function receipt(Sale $sale)
    {
        $sale->load(['items.medicine.manufacturer', 'items.medicine.tax', 'items.inventoryBatch', 'customer', 'branch']);
        return view('sales.receipt', compact('sale'));
    }
}
