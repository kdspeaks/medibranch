<?php

use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return cache()->rememberForever("settings.{$key}", function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }
}
if (! function_exists('activeBranch')) {
    function activeBranch(): ?Branch
    {
        return Cache::rememberForever('branch', function () {
            $branchId = setting('site_branch_id'); // or however you're storing this
            return Branch::find($branchId);
        });
    }
}
