@php
    $record = $getRecord();
    $livewire = $getLivewire();
    $filterState = $livewire->getTableFilterState('branch_id');
    $selectedBranchId = !empty($filterState['value']) ? $filterState['value'] : null;

    $user = auth()->user();
    $userBranches = $user?->hasRole('Super Admin')
        ? \App\Models\Branch::where('is_active', true)->get()
        : ($user?->branches()->where('is_active', true)->get() ?? collect());

    $inventories = $record->inventories;

    if ($selectedBranchId) {
        $scopedInventory = $inventories->firstWhere('branch_id', $selectedBranchId);
        $totalStock = $scopedInventory ? $scopedInventory->quantity : 0;
        $showBreakdown = false;
        $branchBreakdown = collect();
    } else {
        $totalStock = $inventories->sum('quantity');
        $showBreakdown = $userBranches->count() > 1;

        if ($showBreakdown) {
            $branchBreakdown = $userBranches->map(function ($branch) use ($inventories) {
                $inv = $inventories->firstWhere('branch_id', $branch->id);
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'quantity' => $inv ? $inv->quantity : 0,
                ];
            });
        } else {
            $branchBreakdown = collect();
        }
    }

    $colorPalettes = [
        [
            'active' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60',
            'empty' => 'bg-blue-50/40 text-blue-600/70 border-blue-200/50 dark:bg-blue-950/20 dark:text-blue-400/60 dark:border-blue-900/40',
            'dot' => 'bg-blue-500 dark:bg-blue-400',
        ],
        [
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
            'empty' => 'bg-emerald-50/40 text-emerald-600/70 border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-400/60 dark:border-emerald-900/40',
            'dot' => 'bg-emerald-500 dark:bg-emerald-400',
        ],
        [
            'active' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/60',
            'empty' => 'bg-purple-50/40 text-purple-600/70 border-purple-200/50 dark:bg-purple-950/20 dark:text-purple-400/60 dark:border-purple-900/40',
            'dot' => 'bg-purple-500 dark:bg-purple-400',
        ],
        [
            'active' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
            'empty' => 'bg-amber-50/40 text-amber-600/70 border-amber-200/50 dark:bg-amber-950/20 dark:text-amber-400/60 dark:border-amber-900/40',
            'dot' => 'bg-amber-500 dark:bg-amber-400',
        ],
        [
            'active' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
            'empty' => 'bg-rose-50/40 text-rose-600/70 border-rose-200/50 dark:bg-rose-950/20 dark:text-rose-400/60 dark:border-rose-900/40',
            'dot' => 'bg-rose-500 dark:bg-rose-400',
        ],
        [
            'active' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800/60',
            'empty' => 'bg-cyan-50/40 text-cyan-600/70 border-cyan-200/50 dark:bg-cyan-950/20 dark:text-cyan-400/60 dark:border-cyan-900/40',
            'dot' => 'bg-cyan-500 dark:bg-cyan-400',
        ],
        [
            'active' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/60',
            'empty' => 'bg-indigo-50/40 text-indigo-600/70 border-indigo-200/50 dark:bg-indigo-950/20 dark:text-indigo-400/60 dark:border-indigo-900/40',
            'dot' => 'bg-indigo-500 dark:bg-indigo-400',
        ],
        [
            'active' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800/60',
            'empty' => 'bg-teal-50/40 text-teal-600/70 border-teal-200/50 dark:bg-teal-950/20 dark:text-teal-400/60 dark:border-teal-900/40',
            'dot' => 'bg-teal-500 dark:bg-teal-400',
        ],
    ];
@endphp

<div class="flex flex-col gap-1 px-3 py-1">
    {{-- Main / Total Stock Badge --}}
    <div class="flex items-center gap-1.5">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $totalStock > 0 ? 'bg-success/15 text-success dark:text-success-dark border border-success/30' : 'bg-error/15 text-error dark:text-error-dark border border-error/30' }}">
            @if(!$selectedBranchId && $showBreakdown)
                {{ __('messages.total') ?? 'Total' }}: {{ $totalStock }}
            @else
                {{ $totalStock }}
            @endif
        </span>
    </div>

    {{-- Per-Branch Stock Breakdown with distinct branch colors --}}
    @if($showBreakdown && $branchBreakdown->isNotEmpty())
        <div class="flex flex-wrap gap-1.5 mt-0.5 max-w-xs">
            @foreach($branchBreakdown as $index => $b)
                @php
                    $palette = $colorPalettes[$index % count($colorPalettes)];
                    $hasStock = $b['quantity'] > 0;
                    $chipStyle = $hasStock ? $palette['active'] : $palette['empty'];
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $chipStyle }}" title="{{ $b['name'] }}: {{ $b['quantity'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $hasStock ? $palette['dot'] : 'bg-gray-400 dark:bg-gray-600' }}"></span>
                    <span class="truncate max-w-[85px]">{{ $b['name'] }}:</span>
                    <span class="font-bold {{ $hasStock ? 'text-current' : 'text-error dark:text-error-dark' }}">
                        {{ $b['quantity'] }}
                    </span>
                </span>
            @endforeach
        </div>
    @endif
</div>
