<?php

namespace App\Helpers;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;




class Core
{

    public function getACL()
    {
        return collect(config('acl'))->sortBy('sort');;
    }

    public function getSupportedLocales()
    {
        return LaravelLocalization::getSupportedLocales();
    }

    public function getSupportedLanguagesKeys()
    {
        return LaravelLocalization::getSupportedLanguagesKeys();
    }


    public function getCurrentLocaleName()
    {
        return LaravelLocalization::getCurrentLocaleName();
    }


    public function getCurrentLocale()
    {
        return LaravelLocalization::getCurrentLocale();
    }

    public function getLocalesOrder()
    {
        return LaravelLocalization::getLocalesOrder();
    }

    public function getCurrentLocaleDirection()
    {
        return LaravelLocalization::getCurrentLocaleDirection();
    }
}
