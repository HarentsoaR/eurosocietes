<?php

namespace App\Support;

use RuntimeException;

/**
 * Validates required configuration variables at startup.
 * Fails fast with an explicit message instead of surfacing obscure errors later.
 */
class EnvironmentValidator
{
    /**
     * Variables that must always be present with a value.
     *
     * @var list<string>
     */
    private const ALWAYS_REQUIRED = [
        'APP_KEY',
        'APP_ENV',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    /**
     * Variables that must be present when running outside local development.
     *
     * @var list<string>
     */
    private const PRODUCTION_REQUIRED = [
        'REDIS_HOST',
        'REDIS_PORT',
    ];

    public static function validate(): void
    {
        $missing = array_filter(
            self::ALWAYS_REQUIRED,
            static fn (string $key): bool => empty(env($key)),
        );

        $environment = env('APP_ENV', 'production');

        if ($environment !== 'local') {
            $missing = array_merge(
                $missing,
                array_filter(
                    self::PRODUCTION_REQUIRED,
                    static fn (string $key): bool => empty(env($key)),
                ),
            );
        }

        if ($missing !== []) {
            $keys = implode(', ', $missing);

            throw new RuntimeException(
                "Configuration incomplete. Missing required environment variables: {$keys}. "
                . 'Set them in your .env file before starting the application.'
            );
        }
    }
}
