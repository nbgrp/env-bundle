<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace NbGroup\Symfony;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

if (class_exists(CsvEnvVarProcessor::class, false)) {
    return;
}

final class CsvEnvVarProcessor implements EnvVarProcessorInterface
{
    /** @var array<string, string> */
    private $delimiterMap;

    /**
     * @param array<string, string> $delimiterMap
     */
    public function __construct(array $delimiterMap)
    {
        $this->delimiterMap = $delimiterMap;
    }

    public static function getProvidedTypes(): array
    {
        /* NB */
        throw new BadMethodCallException('Unexpected call of '.__FUNCTION__); /* NB */
    }

    /**
     * @return non-empty-list<string|null>
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv): array
    {
        if (\array_key_exists($prefix, $this->delimiterMap) === false) {
            throw new RuntimeException('There is no delimiter for prefix "'.$prefix.'"');
        }

        $delimiter = $this->delimiterMap[$prefix];
        $env = $getEnv($name);

        /** @phpstan-ignore-next-line */
        return str_getcsv($env, $delimiter, '"', \PHP_VERSION_ID >= 70400 ? '' : '\\');
    }
}
