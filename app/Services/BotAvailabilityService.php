<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Class BotAvailabilityService
 * Checks if external bot services (like NEATO) are available
 */
class BotAvailabilityService
{
    private Client $guzzleClient;
    private array $availabilityCache = [];
    private const CACHE_TTL = 30; // seconds

    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Check if a bot service is available at the given URL
     *
     * @param string $baseUrl
     * @return bool
     */
    public function isAvailable(string $baseUrl): bool
    {
        $cacheKey = md5($baseUrl);

        // Check cache first
        if (isset($this->availabilityCache[$cacheKey])) {
            $cached = $this->availabilityCache[$cacheKey];
            if (time() - $cached['timestamp'] < self::CACHE_TTL) {
                return $cached['available'];
            }
        }

        try {
            $response = $this->guzzleClient->head($baseUrl, [
                'timeout' => 2,
                'connect_timeout' => 2,
            ]);

            $available = $response->getStatusCode() < 400;
            $this->cacheResult($cacheKey, $available);
            return $available;
        } catch (\Exception $e) {
            Log::debug("Bot service unavailable at {$baseUrl}: {$e->getMessage()}");
            $this->cacheResult($cacheKey, false);
            return false;
        }
    }

    private function cacheResult(string $cacheKey, bool $available): void
    {
        $this->availabilityCache[$cacheKey] = [
            'available' => $available,
            'timestamp' => time(),
        ];
    }
}
