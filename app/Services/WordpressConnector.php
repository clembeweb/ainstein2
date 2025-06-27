<?php

namespace App\Services;

use GuzzleHttp\Client;

class WordpressConnector
{
    public function __construct(protected Client $client = new Client()) {}

    public function publish(string $siteUrl, array $payload): bool
    {
        try {
            $this->client->post(rtrim($siteUrl, '/') . '/wp-json/wp/v2/posts', [
                'headers' => [
                    'Authorization' => $payload['token'] ?? '',
                ],
                'json' => $payload,
            ]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function testConnection(string $siteUrl, string $token): bool
    {
        try {
            $this->client->get(rtrim($siteUrl, '/') . '/wp-json/wp/v2/posts', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
