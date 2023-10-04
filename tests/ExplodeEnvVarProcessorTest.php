<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace Nbgrp\Tests\EnvBundle;

use Nbgrp\EnvBundle\CsvEnvVarProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nbgrp\EnvBundle\CsvEnvVarProcessor
 *
 * @internal
 */
final class ExplodeEnvVarProcessorTest extends TestCase
{
    /**
     * @dataProvider provideSuccessCases
     */
    public function testSuccess(array $delimiterMap, string $prefix, string $envValue, array $expected): void
    {
        $processor = new CsvEnvVarProcessor($delimiterMap);

        self::assertSame($expected, $processor->getEnv($prefix, '', static function () use ($envValue) {
            return $envValue;
        }));
    }

    /**
     * @return \Generator<array{array, string, string, list<string>}>
     */
    public function provideSuccessCases(): iterable
    {
        $delimiterMap = [
            'csv_dot' => '.',
            'csv_dash' => '-',
            'csv_colon' => ':',
            'csv_bar' => '|',
        ];

        yield [
            $delimiterMap,
            'csv_dot',
            '1.2."foo.bar"."r@p.@@l".',
            ['1', '2', 'foo.bar', 'r@p.@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv_dash',
            '1-2-"foo-bar"-"r@p-@@l"-',
            ['1', '2', 'foo-bar', 'r@p-@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv_colon',
            '1:2:"foo:bar":"r@p:@@l":',
            ['1', '2', 'foo:bar', 'r@p:@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv_bar',
            '1|2|"foo|bar"|"r@p|@@l"|',
            ['1', '2', 'foo|bar', 'r@p|@@l', ''],
        ];

        yield [
            $delimiterMap,
            'csv_dot',
            '"\".".\.."\""".\.',
            \PHP_VERSION_ID >= 70400
                ? ['\\', '.\\..\\"""', '\\', '']
                : ['\\".', '\\', '', '\"".\\.'],
        ];
    }
}
