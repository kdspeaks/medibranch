# Medibranch Project Instructions

## What This App Is
- Laravel 11 application for a homeopathy medicine shop.
- Current implemented domains: medicines, manufacturers, suppliers, branches, purchases, inventory, roles/permissions, users, and site settings.
- UI stack: Livewire 3 pages, Filament form/schema components, PowerGrid tables, Blade layouts/components, Tailwind/Vite.

## Core Architecture
- Keep page-level orchestration in Livewire page classes and shared form concerns.
- Keep business logic in services:
  - `App\Services\PurchaseService` owns purchase create/update/receive flow.
  - `App\Services\InventoryService` owns stock-in and stock-out flow.
  - `App\Services\PricingService` owns totals/tax rounding logic.
- Models should stay thin and relationship-focused. The static wrappers on `Inventory` are compatibility helpers, not the preferred place for new logic.

## Current Domain Model
- `Medicine`: product master, uses `sale_price` not `selling_price`.
- `Purchase`: purchase header for a branch and supplier.
- `PurchaseItem`: purchase line, stores batch details and `inventory_batch_id` after receipt.
- `Inventory`: branch + medicine aggregate.
- `InventoryBatch`: real stock units with quantity, available quantity, pricing, batch number, manufacturing and expiry dates.
- `InventoryLog`: movement history tied to `inventory_batch_id`, with polymorphic `source`.
- `Branch` and `User`: linked through `branch_user` pivot for branch access.

## Stock Flow Rules
- Creating a purchase in `draft` does not add stock.
- A purchase only adds stock when status is `received`.
- Receiving a purchase creates or updates:
  - one `inventories` row per branch + medicine
  - one `inventory_batches` row per batch (or increments an existing matching batch)
  - one `inventory_logs` row of type `in`
  - `purchase_items.inventory_batch_id`
- Stock-out is batch-based and defaults to FIFO by earliest expiry, then oldest batch creation time.
- If a preferred batch ID is supplied, it is prioritized for deduction.
- Received purchases are intentionally protected from unsafe edits after stock has been added.

## Branch Rules
- Branch access is user-aware now.
- `activeBranch()` is still used as a fallback/global branch helper, mainly for defaults.
- Non-super-admin users should only see data for assigned active branches.
- When adding any purchase or inventory query, make it branch-aware from the start.

## Permission Rules
- Permission names use hyphenated slugs only, for example:
  - `manage-medicines`
  - `manage-purchases`
  - `manage-suppliers`
- Do not introduce space-separated permission names.

## UI Conventions
- Routes are defined in `routes/web.php` and mostly map directly to Livewire page classes.
- Medicine and purchase forms are centralized in:
  - `app/Livewire/Pages/Medicines/Concerns/HasMedicineForm.php`
  - `app/Livewire/Pages/Purchase/Concerns/HasPurchaseForm.php`
- Reuse existing Filament components and patterns before inventing new UI structure.
- Sidebar active-state handling for parameterized routes was fixed via route URI matching; be careful not to regress it.

## Known Legacy / Cleanup Areas
- Root `README.md` is still default Laravel boilerplate and does not describe this app.
- `resources/views/components/ui/sidebar.blade bak.php` is an old backup file.
- `resources/views/livewire/components/old/*` contains legacy view fragments.
- `routes/web.php` still contains a large commented-out older route block and some mojibake comments.
- Vendor-published PowerGrid views exist under `resources/views/vendor/livewire-powergrid`; edit only when intentionally customizing PowerGrid output.

## Data / Validation Conventions
- Prefer model casts for dates, decimals, booleans, and statuses.
- Money/tax calculations should go through `PricingService` instead of ad hoc arithmetic.
- Preserve uniqueness around `barcode` and `sku`.
- Keep purchase item dates mapped correctly:
  - `mfg_date`
  - `expiry_date`

## Testing Expectations
- Feature coverage already exists around:
  - purchase receiving
  - inventory batch/log linking
  - FIFO deduction
  - branch restriction
  - medicine view stock/history rendering
- Run at least:
  - `php artisan test`
- For changes touching purchases, inventory, medicine view, permissions, or branch access, add or update focused feature tests.

## How To Extend Safely
- New invoicing work should consume inventory through the service layer, not direct batch mutation in controllers/components.
- Future customer support should plug into the existing branch-aware and batch-aware flow.
- For multi-branch evolution, prefer expanding the `branch_user` and scoped-query pattern rather than adding more global branch state.

## Practical Editing Guidance
- Prefer editing app code under `app/`, `resources/views/livewire/`, `resources/views/components/ui/`, `routes/`, `database/`, and `tests/`.
- Avoid touching `vendor/` and `node_modules/`.
- Be cautious with files that look like backups or obsolete copies unless the task is explicit cleanup.
