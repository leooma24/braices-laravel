<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeocodingService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.google_maps.api_key');
    }

    public function getCoordinates($address)
    {
        $response = $this->client->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'query' => [
                'address' => $address,
                'key' => $this->apiKey,
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['status'] === 'OK') {
            $location = $data['results'][0]['geometry']['location'];
            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
            ];
        }

        return null;
    }
}
