<?php

if (!function_exists('core')) {
    /**
     * Core helper.
     *
     * @return App\Helpers\Helpers\Core
     */
    function core()
    {
        return app()->make(App\Helpers\Core::class);
    }
}

if (!function_exists('ipInfo')) {
    /**
     * Core helper.
     *
     * @return App\Helpers\Helpers\IpInfo
     */
    function ipInfo()
    {
        return app()->make(App\Helpers\IpInfo::class);
    }
}
