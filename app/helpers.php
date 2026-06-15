<?php

if (! function_exists('setting')) {
    /**
     * Get a site setting value from the database.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\SiteSetting::get($key, $default);
    }
}
