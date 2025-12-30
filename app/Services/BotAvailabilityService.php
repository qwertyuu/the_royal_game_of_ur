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
            // POST a minimal request to /infer endpoint
            $this->guzzleClient->post($baseUrl . '/infer', [
                'timeout' => 2,
                'connect_timeout' => 2,
                'body' => json_encode([
                    'pawn_per_player' => 0,
                    'ai_pawn_out' => 0,
                    'enemy_pawn_out' => 0,
                    'dice' => 0,
                    'ai_pawn_positions' => [],
                    'enemy_pawn_positions' => [],
                ]),
            ]);

            $this->cacheResult($cacheKey, true);
            return true;
        } catch (\Exception $e) {
            // Consider service unavailable for connection issues
            $message = $e->getMessage();
            if (strpos($message, 'Connection') !== false ||
                strpos($message, 'resolve') !== false ||
                strpos($message, 'timeout') !== false ||
                strpos($message, 'Empty reply') !== false) {
                Log::debug("Bot service unavailable at {$baseUrl}: {$message}");
                $this->cacheResult($cacheKey, false);
                return false;
            }

            // Service responded (even with error), so it's available
            Log::debug("Bot service available at {$baseUrl} but rejected request: {$message}");
            $this->cacheResult($cacheKey, true);
            return true;
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
