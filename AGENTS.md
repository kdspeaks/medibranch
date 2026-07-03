# Medibranch Project Instructions

## What This App Is
- Laravel 11 application for a homeopathy medicine shop.
- Implemented domains: medicines, manufacturers, suppliers, medicine forms/units, taxes, branches, purchases, inventory, customers, sales/POS, roles/permissions, users, and site settings.
- UI stack: Livewire 3 page classes, Filament 4 tables/forms/actions, Blade layouts/components, Tailwind v4/Vite.
- **No PowerGrid** — all data tables are built with Filament `Table` + `InteractsWithTable` directly in Livewire page classes (PowerGrid is listed in composer but not actively used).

## Package Versions (Key)
- `filament/tables` ~4.0 (NOT Filament Panel — just the standalone table/form/actions packages)
- `livewire/livewire` ^3.4
- `livewire/volt` ^1.0
- `spatie/laravel-permission` ^6.18
- `maatwebsite/excel` ^3.1 (used for medicine import via `App\Imports\MedicinesImport`)
- `blade-ui-kit/blade-icons` (heroicons used throughout)
- `symfony/intl` ^7.3

## Directory Structure
```
app/
  Console/           # Artisan commands
  DTOs/              # Data Transfer Objects (PurchaseData, PurchaseItemData, MedicineData)
  Exceptions/        # Exception handlers
  Forms/
    Schemas/         # Filament form schema classes (MedicineFormSchema, PurchaseFormSchema)
  Http/
    Controllers/     # Only SaleController (receipt printing) — most logic is in Livewire
    Middleware/
    Kernel.php
  Imports/           # MedicinesImport (Maatwebsite Excel)
  Livewire/
    Actions/         # Livewire standalone actions (Logout, modal Delete/ChangeRole/CreatePermission)
    Components/
      Ui/
        Modal/       # Base, Delete, ChangeRole, CreatePermission modal components
    Pages/           # All page-level Livewire components
      Branches/      # BranchList
      Customers/     # CustomerList, CustomerView
      Manufacturers/ # (within Medicines pages)
      Medicines/
        Components/  # Medicine sub-components
        Concerns/    # HasMedicineForm
        ManufacturerList, MedicineCreate, MedicineEdit, MedicineFormList,
        MedicineList, MedicineSearch, MedicineView, TaxList
      Purchase/
        Concerns/    # HasPurchaseForm
        PurchaseCreate, PurchaseEdit, PurchaseList, PurchaseView
      Roles/         # RoleList, PermissionList, RoleTable
      Sales/
        Concerns/    # HasSaleTable (shared sale table columns/filters/actions trait)
        PosTerminal, SaleList, SaleView
      Settings/      # SiteSettings
      Supplier/      # SupplierList
      Users/         # UserList
      Dashboard.php
    Forms/           # (currently empty schemas subdir — use app/Forms/Schemas instead)
  Models/            # All Eloquent models
  Policies/          # InventoryPolicy, MedicinePolicy, PurchasePolicy
  PowerGridThemes/   # Kept for compat but not actively used
  Services/          # Business logic services
  Tables/
    Schemas/         # PowerGrid-style table schema classes (MedicineTableSchema, PurchaseTableSchema)
  View/              # View composers/components
  helpers.php        # Global helpers: activeBranch(), setting(), currency()

resources/
  lang/
    en/messages.php  # English translations
    bn/messages.php  # Bengali translations
  views/
    components/
      ui/            # sidebar, sidebar-link, sidebar-dropdown, sidebar-subitem,
                     # button, input, checkbox, theme-toggle, language-switch
      forms/         # shared form partials
      datatable/     # datatable partials
      base-modal, modal, dropdown, nav-link, etc.
    layouts/         # app.blade.php layout
    livewire/
      pages/         # mirrors app/Livewire/Pages structure
      customers/     # customer-list, customer-view
      sales/         # pos-terminal, sale-list, sale-view, drafts-modal
      components/    # shared Livewire component views
    pages/           # static pages (profile, etc.)
    sales/           # sale receipt view (used by SaleController)
    vendor/          # published vendor views (do not edit unless intentional)

database/
  factories/         # BranchFactory, MedicineFactory, UserFactory
  migrations/
  seeders/
```

## Core Architecture
- **Page-level orchestration** lives in Livewire page classes under `app/Livewire/Pages/`.
- **Shared table columns/actions** extracted to Concerns traits (e.g. `HasSaleTable` shared between `SaleList` and `CustomerView`).
- **Form schemas** centralized in `app/Forms/Schemas/` (`MedicineFormSchema`, `PurchaseFormSchema`) and referenced from page classes.
- **Business logic** belongs in services — never in controllers or Livewire components directly:
  - `App\Services\PurchaseService` — purchase create/update/receive flow.
  - `App\Services\InventoryService` — stock-in and stock-out flow.
  - `App\Services\SaleService` — checkout, invoice generation, stock deduction orchestration.
  - `App\Services\MedicineService` — medicine search/lookup helpers.
  - `App\Services\PricingService` — totals/tax rounding logic.
- **DTOs** in `app/DTOs/` (PurchaseData, PurchaseItemData, MedicineData) are used to pass structured data into services.
- Models stay thin and relationship-focused.

## Domain Model
| Model | Key Notes |
|---|---|
| `Medicine` | `sale_price` (not `selling_price`), `purchase_price`, `margin`, `tax_id`, `is_tax_inclusive`, uses `SoftDeletes` |
| `Tax` | `rate`, `is_active`, uses `SoftDeletes` |
| `MedicineForm` | form type (tablet, liquid, etc.) |
| `MedicineUnit` | unit of measure (mg, ml, etc.) |
| `Manufacturer` | medicine manufacturer |
| `Supplier` | medicine supplier |
| `Purchase` | header for branch + supplier, statuses: `draft` / `received` |
| `PurchaseItem` | line item, stores `inventory_batch_id` after receipt, `mfg_date`, `expiry_date` |
| `Inventory` | branch + medicine aggregate (total quantity) |
| `InventoryBatch` | real batch units: `quantity`, `available_quantity`, `batch_number`, `mfg_date`, `expiry_date` |
| `InventoryLog` | stock movement history, `type` = `in`/`out`, polymorphic `source` |
| `Branch` | branch with `code`, `gst_number`; linked to `User` via `branch_user` pivot |
| `User` | Spatie roles/permissions, `branches()` relationship |
| `Customer` | `name`, `phone`, `email`, `address`; has `sales()` |
| `Sale` | invoice, `invoice_number` format: `INV-{BRANCH_CODE}-{ID}`, `payment_method`, `payment_status`, `round_off` |
| `SaleItem` | sale line: `medicine_id`, `inventory_batch_id`, `unit_price`, `tax_amount`, `sub_total`, `total_amount` |
| `Setting` | key-value site settings (cached via `setting()` helper) |
| `PosDraft` | POS draft save (migration exists, model stub is empty — not yet implemented) |

## Sales / POS Domain
- **POS Terminal** (`App\Livewire\Pages\Sales\PosTerminal`) is the main checkout UI.
  - Cart state is managed entirely server-side in the Livewire component.
  - Barcode/SKU exact match triggers `exact-match-found` browser event.
  - Checkout delegates to `SaleService::checkout()`.
  - Invoice number format: `INV-{BRANCH_CODE}-{SALE_ID}`.
- **SaleService::checkout()** wraps the entire checkout in a DB transaction:
  1. Creates the `Sale` with a temporary invoice number.
  2. Calls `InventoryService::stockOut()` per cart item.
  3. Creates `SaleItem` rows.
  4. Re-links `InventoryLog` entries from `Sale` → `SaleItem` source for precision.
  5. Updates totals and final invoice number.
- **PosDraft**: migration and model stub exist but the model class is empty — do not assume it has any relationships or behaviour until implemented.
- **SaleController** only handles receipt printing (`GET /sales/{sale}/receipt` → `sales.receipt` view).

## Customer Domain
- `CustomerList` — Filament table with create/edit/delete actions inline.
- `CustomerView` — Customer profile page showing stats + sale history table (uses `HasSaleTable` concern).
- Customer uniqueness is enforced on `phone`.

## Stock Flow Rules
- Draft purchase → no stock change.
- `received` purchase → creates/updates `inventories`, `inventory_batches`, `inventory_logs` (type `in`), and links `purchase_items.inventory_batch_id`.
- Stock-out: FIFO by earliest `expiry_date`, then oldest `created_at`. If `preferredBatchId` is supplied it is prioritized.
- Received purchases are protected from unsafe edits.

## Branch & Auth Rules
- `activeBranch()` helper: returns first active branch for non-super-admin users, or the configured site branch for super admins.
- `currency()` helper: returns the configured currency symbol (default `₹`).
- `setting($key, $default)` helper: cached key-value store via `Setting` model.
- Non-super-admin users only see data scoped to their active assigned branches.
- Always make new purchase/inventory/sale queries branch-aware.

## Permission Rules
- Permission slugs are **hyphenated only**: `manage-medicines`, `manage-purchases`, `manage-sales`, `manage-pos`, `manage-customers`, `manage-suppliers`, `manage-manufacturers`, `manage-branches`, `manage-users`, `manage-roles-permission`, `manage-settings`.
- Do not use space-separated permission names.
- Policies: `MedicinePolicy`, `PurchasePolicy`, `InventoryPolicy` exist — check them before adding gate logic.

## Filament Usage (Standalone, NOT Panel)
- This project uses **Filament standalone packages** (`filament/tables`, `filament/forms`, `filament/actions`, `filament/notifications`) — there is no Filament Panel/Admin.
- **CRITICAL — Actions Namespace**: Always use `Filament\Actions\Action`, `Filament\Actions\EditAction`, `Filament\Actions\DeleteAction`, `Filament\Actions\CreateAction`. **Never** use `Filament\Tables\Actions\Action` — it will cause a `Class not found` error.
- Livewire page components that use Filament tables implement `HasForms`, `HasTable`, `HasActions` interfaces and use the corresponding `InteractsWith*` traits.
- Table columns, filters, and actions from `Filament\Tables\Columns\*`, `Filament\Tables\Filters\*` are fine; only the top-level `Action` class must come from `Filament\Actions`.
- Filament `Notification::make()->title(...)->success()->send()` is used for flash notifications.

## UI Conventions
- Layout: `#[Layout('layouts.app')]` attribute on all page components.
- Sidebar: `resources/views/components/ui/sidebar.blade.php` (active-state via route URI matching).
- UI components in `resources/views/components/ui/`: `button`, `input`, `checkbox`, `theme-toggle`, `language-switch`, `sidebar-link`, `sidebar-dropdown`, `sidebar-subitem`.
- **Localization (MANDATORY)**: Every page, component, and menu item MUST use `__('messages.key')`. Add keys to BOTH `resources/lang/en/messages.php` AND `resources/lang/bn/messages.php`. Never hardcode display strings.
- Dark mode is supported — new views must include `dark:` variants matching existing patterns.
- All routes use named routes; prefer `route('name', $model)` over manual URL construction.

## Routes (Current Active Routes)
```
/                       → redirect to login
/dashboard              → Dashboard
/profile                → profile (view)
/users                  → UserList
/roles                  → RoleList
/permissions            → PermissionList
/settings/site          → SiteSettings [can:manage-settings]
/branches               → BranchList [can:manage-branches]
/customers              → CustomerList [can:manage-customers]
/customers/view/{customer} → CustomerView [can:manage-customers]
/pos                    → PosTerminal [can:manage-pos]
/sales                  → SaleList [can:manage-sales]
/sales/{sale}/receipt   → SaleController@receipt [can:manage-sales]
/sales/view/{sale}      → SaleView [can:manage-sales]
/medicines/list         → MedicineList [can:manage-medicines]
/medicines/create       → MedicineCreate [can:manage-medicines]
/medicines/edit/{m}     → MedicineEdit [can:manage-medicines]
/medicines/view/{m}     → MedicineView [can:manage-medicines]
/medicines/manufacturers → ManufacturerList [can:manage-manufacturers]
/medicines/forms        → MedicineFormList [can:manage-settings]
/medicines/taxes        → TaxList [can:manage-settings]
/medicines/suppliers    → SupplierList [can:manage-suppliers]
/medicines/purchases/list   → PurchaseList [can:manage-purchases]
/medicines/purchases/create → PurchaseCreate [can:manage-purchases]
/medicines/purchases/view/{p} → PurchaseView [can:manage-purchases]
/medicines/purchases/edit/{p} → PurchaseEdit [can:manage-purchases]
/lang/{locale}          → session locale switch
```

## Data / Validation Conventions
- Prefer model `casts()` method for dates, decimals, booleans (most models already do this).
- Money/tax calculations go through `PricingService`.
- Uniqueness: `barcode` and `sku` on medicines; `phone` on customers.
- Purchase item dates: `mfg_date`, `expiry_date` (not `manufacture_date` or `manufactured_at`).

## Existing Factories
- `BranchFactory`, `MedicineFactory`, `UserFactory` exist.
- **No factory** for: Customer, Sale, Purchase, Inventory, Tax — create manually in tests or add factories as needed.

## Testing Expectations
- Test framework: **PHPUnit** (not Pest). Use `php artisan make:test --phpunit {name}`.
- Existing feature tests: `FoundationCleanupTest`, `SaleCheckoutTest`, `MedicineViewTest`, `ProfileTest`, `Auth/*`.
- Coverage exists for: purchase receiving, batch/log linking, FIFO deduction, branch restriction, medicine view, sale checkout.
- For any change touching purchases, inventory, sales/POS, medicine view, permissions, or branch access: add or update a focused feature test.
- Run only the minimal set: `php artisan test tests/Feature/SomethingTest.php` or `--filter=testName`.

## How To Extend Safely
- New features consuming stock must go through `InventoryService::stockOut()` — never mutate batches directly.
- New customer-facing flows should reuse `HasSaleTable` concern for consistent sale table rendering.
- New Filament table pages follow the `HasForms + HasTable + HasActions` + `InteractsWith*` pattern.
- New permissions must be registered in the seeder/permission tables and use hyphenated slugs.
- For multi-branch expansion, extend the `branch_user` pivot + scoped-query pattern; avoid adding global branch state.

## Known Legacy / Cleanup Areas
- `resources/views/components/ui/sidebar.blade bak.php` — old backup, do not edit.
- `routes/web.php` — contains a large commented-out legacy route block (lines ~29–102); do not uncomment or duplicate.
- `app/PowerGridThemes/` — exists but PowerGrid is not actively used in new code.
- `app/Tables/Schemas/` — `MedicineTableSchema`, `PurchaseTableSchema` exist but are not the primary table pattern (Filament tables are used instead).
- `app/Models/PosDraft.php` — empty stub; migration `create_pos_drafts_table` exists. Implement before using.
- `scratch.php` and `rewrite_pos.py` in project root — scratch/utility files, not part of the app.

## Practical Editing Guidance
- Primary edit paths: `app/`, `resources/views/livewire/`, `resources/views/components/ui/`, `resources/lang/`, `routes/`, `database/`, `tests/`.
- Avoid: `vendor/`, `node_modules/`, files with `.bak.` in the name.
- After PHP changes run: `vendor/bin/pint --dirty`.
- After Blade/CSS changes the user may need to run `npm run dev` or `npm run build`.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.28
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v10
- rector/rector (RECTOR) - v2
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel 11 file structure.
- This is **perfectly fine** and recommended by Laravel. Follow the existing structure from Laravel 10. We do not to need migrate to the Laravel 11 structure unless the user explicitly requests that.

### Laravel 10 Structure
- Middleware typically live in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class `
    - `php artisan make:interface `


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== volt/core rules ===

## Livewire Volt

- This project uses Livewire Volt for interactivity within its pages. New pages requiring interactivity must also use Livewire Volt. There is documentation available for it.
- Make new Volt components using `php artisan make:volt [name] [--test] [--pest]`
- Volt is a **class-based** and **functional** API for Livewire that supports single-file components, allowing a component's PHP logic and Blade templates to co-exist in the same file
- Livewire Volt allows PHP logic and Blade templates in one file. Components use the `@volt` directive.
- You must check existing Volt components to determine if they're functional or class based. If you can't detect that, ask the user which they prefer before writing a Volt component.

### Volt Functional Component Example

<code-snippet name="Volt Functional Component Example" lang="php">
@volt
<?php
use function Livewire\Volt\{state, computed};

state(['count' => 0]);

$increment = fn () => $this->count++;
$decrement = fn () => $this->count--;

$double = computed(fn () => $this->count * 2);
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <h2>Double: {{ $this->double }}</h2>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
@endvolt
</code-snippet>


### Volt Class Based Component Example
To get started, define an anonymous class that extends Livewire\Volt\Component. Within the class, you may utilize all of the features of Livewire using traditional Livewire syntax:


<code-snippet name="Volt Class-based Volt Component Example" lang="php">
use Livewire\Volt\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
} ?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
</code-snippet>


### Testing Volt & Volt Components
- Use the existing directory for tests if it already exists. Otherwise, fallback to `tests/Feature/Volt`.

<code-snippet name="Livewire Test Example" lang="php">
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
</code-snippet>


<code-snippet name="Volt Component Test Using Pest" lang="php">
declare(strict_types=1);

use App\Models\{User, Product};
use Livewire\Volt\Volt;

test('product form creates product', function () {
    $user = User::factory()->create();

    Volt::test('pages.products.create')
        ->actingAs($user)
        ->set('form.name', 'Test Product')
        ->set('form.description', 'Test Description')
        ->set('form.price', 99.99)
        ->call('create')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});
</code-snippet>


### Common Patterns


<code-snippet name="CRUD With Volt" lang="php">
<?php

use App\Models\Product;
use function Livewire\Volt\{state, computed};

state(['editing' => null, 'search' => '']);

$products = computed(fn() => Product::when($this->search,
    fn($q) => $q->where('name', 'like', "%{$this->search}%")
)->get());

$edit = fn(Product $product) => $this->editing = $product->id;
$delete = fn(Product $product) => $product->delete();

?>

<!-- HTML / UI Here -->
</code-snippet>

<code-snippet name="Real-Time Search With Volt" lang="php">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />
</code-snippet>

<code-snippet name="Loading States With Volt" lang="php">
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>
