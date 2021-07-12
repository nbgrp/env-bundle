<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace NbGroup\Symfony;

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
        ];
    }

    /**
     * @return array<array-key, bool|int|float|string>
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv): array
    {
        $env = (array) $getEnv($name);

        if ($prefix === 'bool-array') {
            return array_map(
                static function ($value): bool {
                    return (bool) (filter_var($value, \FILTER_VALIDATE_BOOLEAN, ['flags' => \FILTER_NULL_ON_FAILURE]) ?? filter_var($value, \FILTER_VALIDATE_INT) ?: filter_var($value, \FILTER_VALIDATE_FLOAT));
                },
                $env
            );
        }

        if ($prefix === 'int-array') {
            return array_map(
                static function ($value) use ($name): int {
                    if ((filter_var($value, \FILTER_VALIDATE_INT) ?: filter_var($value, \FILTER_VALIDATE_FLOAT)) === false) {
                        throw new RuntimeException(sprintf('Non-numeric member of env var "%s" cannot be cast to int.', $name));
                    }

                    return (int) $value;
                },
                $env,
            );
        }

        if ($prefix === 'float-array') {
            return array_map(
                static function ($value) use ($name): float {
                    if (filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
                        throw new RuntimeException(sprintf('Non-numeric member of env var "%s" cannot be cast to float.', $name));
                    }

                    return (float) $value;
                },
                $env,
            );
        }

        // string-array
        return array_map('strval', $env);
    }
}
