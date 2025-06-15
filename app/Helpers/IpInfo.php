<?php

namespace App\Helpers;

use GuzzleHttp\Client;

class IpInfo
{

    public  function getGeolocation($ip)
    {
        $client = new Client();
        $apiKey = env('IPINFO_APIKEY','457abc94300f8f'); // Replace with your actual ipinfo.io API token

        $response = $client->get("https://ipinfo.io/{$ip}?token={$apiKey}");

        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);
            return [
                'country' => $data['country'] ?? 'N/A',
                'city' => $data['city'] ?? 'N/A',
            ];
        }

        return [
            'country' => 'N/A',
            'city' => 'N/A',
        ];
    }
}
