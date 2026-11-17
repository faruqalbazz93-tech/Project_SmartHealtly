<?php

namespace App\Core;

class RateLimiter
{
    /**
     * Check if key has exceeded maximum attempts
     */
    public static function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $currentHits = self::attempts($key);
        return $currentHits >= $maxAttempts;
    }

    /**
     * Increment attempts for key
     */
    public static function hit(string $key, int $decaySeconds = 60): int
    {
        $now = time();

        try {
            if (Database::isConnected()) {
                $pdo = Database::getConnection();
                
                // Get existing limit
                $stmt = $pdo->prepare("SELECT * FROM `rate_limits` WHERE `rate_key` = :k LIMIT 1");
                $stmt->execute(['k' => $key]);
                $record = $stmt->fetch();

                if ($record) {
                    // Check if decay has passed
                    if ($now - (int)$record['last_attempt_at'] > (int)$record['decay_seconds']) {
                        // Reset
                        $update = $pdo->prepare("UPDATE `rate_limits` SET `hits` = 1, `last_attempt_at` = :now, `decay_seconds` = :decay WHERE `rate_key` = :k");
                        $update->execute(['now' => $now, 'decay' => $decaySeconds, 'k' => $key]);
                        return 1;
                    }

                    // Increment
                    $newHits = (int)$record['hits'] + 1;
                    $update = $pdo->prepare("UPDATE `rate_limits` SET `hits` = :hits, `last_attempt_at` = :now WHERE `rate_key` = :k");
                    $update->execute(['hits' => $newHits, 'now' => $now, 'k' => $key]);
                    return $newHits;
                } else {
                    // Insert new
                    $insert = $pdo->prepare("INSERT INTO `rate_limits` (`rate_key`, `hits`, `last_attempt_at`, `decay_seconds`) VALUES (:k, 1, :now, :decay)");
                    $insert->execute(['k' => $key, 'now' => $now, 'decay' => $decaySeconds]);
                    return 1;
                }
            }
        } catch (\Throwable $e) {
            // Fallback to session
        }

        Session::start();
        $sessKey = "rate_limit_{$key}";
        $sessData = $_SESSION[$sessKey] ?? null;

        if ($sessData && ($now - $sessData['time'] <= $sessData['decay'])) {
            $sessData['hits']++;
            $_SESSION[$sessKey] = $sessData;
            return $sessData['hits'];
        }

        $_SESSION[$sessKey] = ['hits' => 1, 'time' => $now, 'decay' => $decaySeconds];
        return 1;
    }

    /**
     * Get current attempts for key
     */
    public static function attempts(string $key): int
    {
        $now = time();

        try {
            if (Database::isConnected()) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT * FROM `rate_limits` WHERE `rate_key` = :k LIMIT 1");
                $stmt->execute(['k' => $key]);
                $record = $stmt->fetch();

                if ($record) {
                    if ($now - (int)$record['last_attempt_at'] > (int)$record['decay_seconds']) {
                        return 0;
                    }
                    return (int)$record['hits'];
                }
            }
        } catch (\Throwable $e) {
            // Fallback to session
        }

        Session::start();
        $sessKey = "rate_limit_{$key}";
        $sessData = $_SESSION[$sessKey] ?? null;

        if ($sessData && ($now - $sessData['time'] <= $sessData['decay'])) {
            return (int)$sessData['hits'];
        }

        return 0;
    }

    /**
     * Get remaining seconds before rate limit resets
     */
    public static function availableIn(string $key): int
    {
        $now = time();

        try {
            if (Database::isConnected()) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT * FROM `rate_limits` WHERE `rate_key` = :k LIMIT 1");
                $stmt->execute(['k' => $key]);
                $record = $stmt->fetch();

                if ($record) {
                    $elapsed = $now - (int)$record['last_attempt_at'];
                    $decay = (int)$record['decay_seconds'];
                    return max(0, $decay - $elapsed);
                }
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        Session::start();
        $sessKey = "rate_limit_{$key}";
        $sessData = $_SESSION[$sessKey] ?? null;

        if ($sessData) {
            $elapsed = $now - $sessData['time'];
            return max(0, $sessData['decay'] - $elapsed);
        }

        return 0;
    }

    /**
     * Clear rate limit for key
     */
    public static function clear(string $key): void
    {
        try {
            if (Database::isConnected()) {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("DELETE FROM `rate_limits` WHERE `rate_key` = :k");
                $stmt->execute(['k' => $key]);
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        Session::start();
        unset($_SESSION["rate_limit_{$key}"]);
    }
}
