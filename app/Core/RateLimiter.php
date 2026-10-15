<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Fixed-window rate limiter backed by the rate_limits table, so limits are
 * shared across PHP workers.
 */
final class RateLimiter
{
    public function __construct(private readonly Database $db)
    {
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $row = $this->db->fetch('SELECT hits, reset_at FROM rate_limits WHERE limiter_key = ?', [$this->hashKey($key)]);
        if (!$row) {
            return false;
        }
        if (strtotime($row['reset_at'] . ' UTC') <= time()) {
            return false;
        }
        return (int) $row['hits'] >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds): int
    {
        $hashed = $this->hashKey($key);
        $resetAt = gmdate('Y-m-d H:i:s', time() + $decaySeconds);
        $this->db->query(
            'INSERT INTO rate_limits (limiter_key, hits, reset_at) VALUES (?, 1, ?)
             ON DUPLICATE KEY UPDATE
               hits = IF(reset_at <= UTC_TIMESTAMP(), 1, hits + 1),
               reset_at = IF(reset_at <= UTC_TIMESTAMP(), VALUES(reset_at), reset_at)',
            [$hashed, $resetAt]
        );
        return (int) $this->db->value('SELECT hits FROM rate_limits WHERE limiter_key = ?', [$hashed]);
    }

    public function availableIn(string $key): int
    {
        $reset = $this->db->value('SELECT reset_at FROM rate_limits WHERE limiter_key = ?', [$this->hashKey($key)]);
        return $reset ? max(0, strtotime($reset . ' UTC') - time()) : 0;
    }

    public function clear(string $key): void
    {
        $this->db->delete('rate_limits', ['limiter_key' => $this->hashKey($key)]);
    }

    /**
     * Consume one attempt; returns false when the limit is exceeded.
     */
    public function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }
        $this->hit($key, $decaySeconds);
        return true;
    }

    private function hashKey(string $key): string
    {
        return hash('sha256', $key);
    }
}
