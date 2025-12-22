<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class GeocodingService
{
    protected $client;
    protected $baseUrl = 'https://nominatim.openstreetmap.org/';
    /**
     * Create a new class instance.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getCoordinatesFromAddress($address)
    {
        // $response = Http::withHeaders([
        //     'User-Agent' => 'LaravelStudentProject/1.0 (email@example.com)',
        //     ])->get('https://nominatim.openstreetmap.org/search', [
        //     'q' => $address,
        //     'format' => 'json',
        //     'limit' => 1
        // ]);

        $response = $this->client->get($this->baseUrl . 'search', [
            'headers' => [
                'User-Agent' => 'CareConnect/1.0 (gustimuhammadgalih@gmail.com)',  // Your app name and contact
            ],
            'query' => [
                'q' => $address,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 1,
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!empty($data)) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon'],
            ];
        }

        return null;
    }

    public function getAddressFromCoordinates($lat, $lon)
    {
        $response = $this->client->get($this->baseUrl . 'reverse', [
            'headers' => [
                'User-Agent' => 'CareConnect/1.0 (gustimuhammadgalih@gmail.com)',  // Your app name and contact
            ],
            'query' => [
                'lat' => $lat,
                'lon' => $lon,
                'format' => 'json',
                'addressdetails' => 1,
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!empty($data)) {
            return $data[0]['display_name'];
        }

        return null;
    }
}
