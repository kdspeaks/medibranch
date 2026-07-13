<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Medicine;
use Carbon\Carbon;

class Dashboard extends Component
{
    public function render()
    {
        $branchId = activeBranch()?->id;

        $todaySalesAmount = Sale::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $todaySalesCount = Sale::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', Carbon::today())
            ->count();

        $totalCustomers = Customer::count();
        $totalMedicines = Medicine::count();

        return view('livewire.pages.dashboard', [
            'todaySalesAmount' => $todaySalesAmount,
            'todaySalesCount' => $todaySalesCount,
            'totalCustomers' => $totalCustomers,
            'totalMedicines' => $totalMedicines,
        ]);
    }
}
