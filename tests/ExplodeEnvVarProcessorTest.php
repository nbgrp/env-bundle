<?php
// SPDX-License-Identifier: BSD-3-Clause

declare(strict_types=1);

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

        self::assertSame($expected, $processor->getEnv($prefix, '', static fn () => $envValue));
    }

    /**
     * @return \Generator<array{array, string, string, list<string>}>
     */
    public function provideSuccessCases(): iterable
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
