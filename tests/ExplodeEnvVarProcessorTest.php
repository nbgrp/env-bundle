<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace NbGroup\Tests\Symfony;

use NbGroup\Symfony\CsvEnvVarProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \NbGroup\Symfony\CsvEnvVarProcessor
 *
 * @internal
 */
final class ExplodeEnvVarProcessorTest extends TestCase
{
    /**
     * @dataProvider successProvider
     */
    public function testSuccess(array $delimiterMap, string $prefix, string $envValue, array $expected): void
    {
        $processor = new CsvEnvVarProcessor($delimiterMap);

        self::assertSame($expected, $processor->getEnv($prefix, '', static function () use ($envValue) {
            return $envValue;
        }));
    }

    /**
     * @return \Generator<array{array, string, string, string[]}>
     */
    public function successProvider(): \Generator
    {
        $delimiterMap = [
            'csv-dot' => '.',
            'csv-dash' => '-',
            'csv-colon' => ':',
            'csv-bar' => '|',
        ];

        yield [
            $delimiterMap,
            'csv-dot',
            '1.2."foo.bar"."r@p.@@l".',
            ['1', '2', 'foo.bar', 'r@p.@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv-dash',
            '1-2-"foo-bar"-"r@p-@@l"-',
            ['1', '2', 'foo-bar', 'r@p-@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv-colon',
            '1:2:"foo:bar":"r@p:@@l":',
            ['1', '2', 'foo:bar', 'r@p:@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv-bar',
            '1|2|"foo|bar"|"r@p|@@l"|',
            ['1', '2', 'foo|bar', 'r@p|@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv-dot',
            '"\".".\.."\""".\.',
            \PHP_VERSION_ID >= 70400
                ? ['\\', '.\\..\\"""', '\\', '']
                : ['\\".', '\\', '', '\"".\\.'],
        ];
    }
}
