<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace Nbgrp\EnvBundle;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

final class ArrayCastEnvVarProcessor implements EnvVarProcessorInterface
{
    public static function getProvidedTypes(): array
    {
        return [
            'bool_array' => 'array',
            'int_array' => 'array',
            'float_array' => 'array',
            'string_array' => 'array',
        ];
    }

    /**
     * @param string $prefix
     * @param string $name
     *
     * @return array<array-key, bool|int|float|string>
     */
    public function getEnv($prefix, $name, \Closure $getEnv): array
    {
        $env = (array) $getEnv($name);

        switch ($prefix) {
            case 'bool_array':
                return array_map(self::getBooleanMapper(), $env);

            case 'int_array':
                return array_map(self::getIntegerMapper($name), $env);

            case 'float_array':
                return array_map(self::getFloatMapper($name), $env);

            default: // string_array
                return array_map('strval', $env);
        }
    }

    /**
     * @psalm-pure
     */
    private static function getBooleanMapper(): callable
    {
        /** @psalm-suppress MissingClosureParamType */
        return static function ($value): bool {
            return (bool) (filter_var($value, \FILTER_VALIDATE_BOOLEAN, ['flags' => \FILTER_NULL_ON_FAILURE]) ?? filter_var($value, \FILTER_VALIDATE_INT) ?: filter_var($value, \FILTER_VALIDATE_FLOAT));
        };
    }

    /**
     * @psalm-pure
     */
    private static function getIntegerMapper(string $name): callable
    {
        /** @psalm-suppress MissingClosureParamType */
        return static function ($value) use ($name): int {
            if ((filter_var($value, \FILTER_VALIDATE_INT) ?: filter_var($value, \FILTER_VALIDATE_FLOAT)) === false) {
                throw new RuntimeException(sprintf('Non-numeric member of env var "%s" cannot be cast to int.', $name));
            }

            return (int) $value;
        };
    }

    /**
     * @psalm-pure
     */
    private static function getFloatMapper(string $name): callable
    {
        /** @psalm-suppress MissingClosureParamType */
        return static function ($value) use ($name): float {
            if (filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
                throw new RuntimeException(sprintf('Non-numeric member of env var "%s" cannot be cast to float.', $name));
            }

            return (float) $value;
        };
    }
}
