<?php
// SPDX-License-Identifier: BSD-3-Clause

declare(strict_types=1);

namespace Nbgrp\EnvBundle;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

final class ArrayCastEnvVarProcessor implements EnvVarProcessorInterface
{
    public static function getProvidedTypes(): array
    {
        return [
            'bool-array' => 'array',
            'int-array' => 'array',
            'float-array' => 'array',
            'string-array' => 'array',
            'base64-array' => 'array',
            'base64url-array' => 'array',
        ];
    }

    /**
     * @return array<array-key, scalar>
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv): array
    {
        $env = (array) $getEnv($name);

        return match ($prefix) {
            'bool-array' => array_map(self::getBooleanMapper(), $env),
            'int-array' => array_map(self::getIntegerMapper($name), $env),
            'float-array' => array_map(self::getFloatMapper($name), $env),
            'base64-array' => array_map(self::getBase64Mapper($name), $env),
            'base64url-array' => array_map(self::getBase64UrlMapper($name), $env),
            /* string-array */ default => array_map(self::getStringMapper($name), $env),
        };
    }

    /**
     * @psalm-pure
     */
    private static function getBooleanMapper(): callable
    {
        return static fn (mixed $value): bool => (bool) (filter_var($value, \FILTER_VALIDATE_BOOLEAN, ['flags' => \FILTER_NULL_ON_FAILURE]) ?? filter_var($value, \FILTER_VALIDATE_INT) ?: filter_var($value, \FILTER_VALIDATE_FLOAT));
    }

    /**
     * @psalm-pure
     */
    private static function getIntegerMapper(string $name): callable
    {
        return static function (mixed $value) use ($name): int {
            if ((filter_var($value, \FILTER_VALIDATE_INT) ?: filter_var($value, \FILTER_VALIDATE_FLOAT)) === false) {
                throw new RuntimeException('Non-numeric member of environment variable "'.$name.'" cannot be cast to int.');
            }

            return (int) $value;
        };
    }

    /**
     * @psalm-pure
     */
    private static function getFloatMapper(string $name): callable
    {
        return static function (mixed $value) use ($name): float {
            if (filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
                throw new RuntimeException('Non-numeric member of environment variable "'.$name.'" cannot be cast to float.');
            }

            return (float) $value;
        };
    }

    /**
     * @psalm-pure
     */
    private static function getBase64Mapper(string $name): callable
    {
        return static function (mixed $value) use ($name): string {
            if (!\is_string($value)) {
                throw new RuntimeException('Environment variable "'.$name.'" must be a valid base64 string.');
            }

            if (\function_exists('sodium_base642bin')) {
                try {
                    return \strlen($value) % 4 === 0
                        ? sodium_base642bin($value, \SODIUM_BASE64_VARIANT_ORIGINAL)
                        : sodium_base642bin($value, \SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING);
                } catch (\SodiumException) {
                    throw new RuntimeException('Environment variable "'.$name.'" must be a valid base64 string.');
                }
            }

            $decoded = base64_decode(str_pad($value, \strlen($value) % 4, '='), true);
            if ($decoded === false) {
                throw new RuntimeException('Environment variable "'.$name.'" must be a valid base64 string.');
            }

            return $decoded;
        };
    }

    /**
     * @psalm-pure
     */
    private static function getBase64UrlMapper(string $name): callable
    {
        return static function (mixed $value) use ($name): string {
            if (!\is_string($value)) {
                throw new RuntimeException('Environment variable "'.$name.'" must be a valid base64url string.');
            }

            if (\function_exists('sodium_base642bin')) {
                try {
                    return \strlen($value) % 4 === 0
                        ? sodium_base642bin($value, \SODIUM_BASE64_VARIANT_URLSAFE)
                        : sodium_base642bin($value, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
                } catch (\SodiumException) {
                    throw new RuntimeException('Environment variable "'.$name.'" must be a valid base64url string.');
                }
            }

            $decoded = base64_decode(str_pad(strtr($value, '-_', '+/'), \strlen($value) % 4, '='), true);
            if ($decoded === false) {
                throw new RuntimeException('Environment variable "'.$name.'" must be a valid base64url string.');
            }

            return $decoded;
        };
    }

    /**
     * @psalm-pure
     */
    private static function getStringMapper(string $name): callable
    {
        return static fn (mixed $value): string => (string) $value;
    }
}
