<?php

namespace App\Providers;

use App\Types\CacheKeysType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Modules\Area\App\Repositories\CityRepository;
use Modules\Area\App\Repositories\StateRepository;
use Modules\Area\App\Repositories\CountryRepository;
use Modules\FAQs\App\Repositories\TopicRepository;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCacheKeys();
    }

    /**
     * Bind cache keys and their closures to the container.
     *
     * @return void
     */
    private function registerCacheKeys()
    {
        $cacheKeys = $this->getCacheKeys();

        foreach ($cacheKeys as $key => $closure) {
            $this->app->singleton($key, $closure);
        }
    }

    /**
     * Get an array of cache keys and their closures.
     *
     * @return array
     */
    private function getCacheKeys()
    {
        $cacheData = [];

        // Fetch dynamic cache keys for topics and countries
        $topicsCacheKeys = CacheKeysType::getTopicsCacheKeys();
        $countriesCacheKeys = CacheKeysType::getCountriesCacheKeys();


        // Register topics cache
        foreach ($topicsCacheKeys as $locale => $topicsCacheKey) {
            $cacheData[$topicsCacheKey] = function () use ($topicsCacheKey, $locale) {
                return Cache::remember(
                    $topicsCacheKey,
                    now()->addDays(5),
                    function () use ($locale) {
                        return app(TopicRepository::class)->getAllActive($locale)->get();
                    }
                );
            };
        }

        // Register countries cache
        foreach ($countriesCacheKeys as $locale => $countriesCacheKey) {
            $cacheData[$countriesCacheKey] = function () use ($countriesCacheKey, $locale) {
                return Cache::remember(
                    $countriesCacheKey,
                    now()->addDays(5),
                    function () use ($locale) {
                        return app(CountryRepository::class)->getAllActive($locale)->get();
                    }
                );
            };
        }


        // Static cache definitions for cities, states
        $cacheData = array_merge($cacheData, [
            // Cities Cache
            CacheKeysType::CITIES_CACHE => function () {
                return Cache::remember(
                    CacheKeysType::CITIES_CACHE,
                    now()->addDays(5),
                    function () {
                        return app(CityRepository::class)->getAll()->get();
                    }
                );
            },

            // States Cache
            CacheKeysType::STATES_CACHE => function () {
                return Cache::remember(
                    CacheKeysType::STATES_CACHE,
                    now()->addDays(5),
                    function () {
                        return app(StateRepository::class)->getAll()->get();
                    }
                );
            },
        ]);

        return $cacheData;
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Additional bootstrapping if needed
    }
}
