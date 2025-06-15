<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheTrait
{

    public function getCache(string $key)
    {
        return Cache::get($key);
    }

    public function setCache(string $key, $value, $timeout)
    {
        return Cache::remember($key, $timeout, function () use ($value) {
            return $value;
        });
    }
    public function deleteCache(string $key)
    {
        return Cache::forget($key);
    }
    public function deleteCaches(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteCache($key);
        }
    }

    public function clearCache()
    {
        return Cache::flush();
    }

    public function getCacheKeys()
    {
        return Cache::keys();
    }
}
