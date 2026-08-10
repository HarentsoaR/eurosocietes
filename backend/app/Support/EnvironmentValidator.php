<?php

namespace App\Support;

use RuntimeException;

/**
 * Validates required configuration values at startup.
 * Fails fast with an explicit message instead of surfacing obscure errors later.
 *
 * Reads resolved config (not raw env) so it remains correct when the
 * configuration cache is active (php artisan config:cache).
 */
class EnvironmentValidator
{
    /**
     * Configuration keys that must always resolve to a non-empty value.
     *
     * @var list<string>
     */
    private const ALWAYS_REQUIRED = [
        'app.key',
        'app.env',
        'app.url',
        'database.default',
    ];

    /**
     * Database connection keys that must resolve to a non-empty value.
     *
     * @var list<string>
     */
    private const DATABASE_REQUIRED = [
        'host',
        'port',
        'database',
        'username',
        'password',
    ];

    /**
     * Configuration keys that must resolve when running outside local development.
     *
     * @var list<string>
     */
    private const PRODUCTION_REQUIRED = [
        'database.redis.default.host',
        'database.redis.default.port',
    ];

    public static function validate(): void
    {
        $missing = array_filter(
            self::ALWAYS_REQUIRED,
            static fn (string $key): bool => empty(config($key)),
        );

        $driver = config('database.default');
        if (is_string($driver)) {
            $missing = array_merge(
                $missing,
                array_filter(
                    self::DATABASE_REQUIRED,
                    static fn (string $key): bool => empty(config("database.connections.{$driver}.{$key}")),
                ),
            );
        }

        if (config('app.env') !== 'local') {
            $missing = array_merge(
                $missing,
                array_filter(
                    self::PRODUCTION_REQUIRED,
                    static fn (string $key): bool => empty(config($key)),
                ),
            );
        }

        if ($missing !== []) {
            $keys = implode(', ', $missing);

            throw new RuntimeException(
                "Configuration incomplete. Missing required configuration values: {$keys}. "
                . 'Set them in your .env file before starting the application.'
            );
        }
    }
}
