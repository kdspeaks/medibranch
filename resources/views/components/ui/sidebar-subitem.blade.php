@props(['route'])

@php
    $routePath = function (string $routeName): string {
        if (! Route::has($routeName)) {
            return '/' . ltrim($routeName, '/');
        }

        $uri = Route::getRoutes()->getByName($routeName)?->uri() ?? $routeName;
        $baseUri = preg_replace('/\{[^}]+\}/', '', $uri);

        return '/' . trim($baseUri, '/');
    };

    $href = Route::has($route) ? route($route) : '/' . ltrim($route, '/');

    // Build route path prefixes instead of concrete URLs so parameterized routes
    // like medicines.view/{medicine} still match the current pathname.
    $rawConsider = $attributes->get('active-consideration', '') ?: '';

    $considerRoutes = collect(explode(',', $rawConsider))
        ->map(fn($r) => trim($r))
        ->filter()
        ->map(fn($r) => $routePath($r))
        ->all();

    $targetPath = $routePath($route);
    // dd($considerRoutes);
@endphp

<li data-href="{{ $href }}" x-data="{
    isActive: false,
    target: '{{ $targetPath }}',
    consideredTargets: {{ json_encode($considerRoutes) }},

    normalize(path) {
        try {
            return new URL(path, window.location.origin).pathname.replace(/\/+$/, '');
        } catch (e) {
            // fallback if path is already a pathname
            return String(path).replace(/\/+$/, '');
        }
    },

    checkActive() {

        const current = window.location.pathname.replace(/\/+$/, '');
        {{-- console.log('Checking active: current=', current, ' target=', this.target, ' considered=', this.consideredTargets); --}}

        // check the primary target
        const mainTarget = this.normalize(this.target);
        if (current === mainTarget || current.startsWith(mainTarget + '/')) {
            this.isActive = true;
            toggleExpanded();
            return;
        }

        // check any considered targets (exact or subpath)
        for (const t of this.consideredTargets) {

            const ct = this.normalize(t);

            if (current === ct || current.startsWith(ct + '/')) {
                console.log('Checking considered target:', ct);
                this.isActive = true;
                toggleExpanded();
                return;
            }
        }

        // optional: also mark active if same base segment (e.g. '/medicines/*' matches '/medicines')
        // const baseSeg = mainTarget.split('/')[1];
        // if (baseSeg && current.startsWith(`/${baseSeg}/`)) {
        //     this.isActive = true;
        //     return;
        // }

        this.isActive = false;
        this.isExpaned = false;
    }
}" x-init="checkActive()" @popstate.window="checkActive()"
    {{-- updates when Livewire or history navigation happens --}}>
    <a href="{{ $href }}" wire:navigate
        x-bind:class="isActive
            ?
            'text-primary bg-surface-dark/10 dark:text-primary-dark dark:bg-surface/10' :
            'text-text hover:bg-surface-dark/10 dark:text-text-dark dark:hover:bg-surface/10'"
        {{ $attributes->merge(['class' => 'flex items-center p-2 pl-11 text-base rounded-lg transition group']) }}>
        {{ $slot }}
    </a>
</li>
