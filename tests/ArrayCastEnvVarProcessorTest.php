<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace NbGroup\Tests\Symfony;

use NbGroup\Symfony\ArrayCastEnvVarProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @covers \NbGroup\Symfony\ArrayCastEnvVarProcessor
 *
 * @internal
 */
final class ArrayCastEnvVarProcessorTest extends TestCase
{
    /**
     * @dataProvider successProvider
     *
     * @phpstan-ignore-next-line
     */
    public function testSuccess(string $prefix, array $envValue, array $expected): void
    {
        $processor = new ArrayCastEnvVarProcessor();

        self::assertSame($expected, $processor->getEnv($prefix, '', static function () use ($envValue): array {
            return $envValue;
        }));
    }

    /**
     * @return \Generator<array{0: string, 1: array, 2: array}>
     */
    public function successProvider(): \Generator
    {
        yield [
            'bool-array',
            [1, 2, 0, -1, 'true', 'false', 'yes', 'no', 'on', 'off', 'any'],
            [true, true, false, true, true, false, true, false, true, false, false],
        ];

        yield [
            'int-array',
            ['0', '1', '-2.0', '3.5', 0, 1, -2.0, 3.5],
            [0, 1, -2, 3, 0, 1, -2, 3],
        ];

        yield [
            'float-array',
            [0.0, -0.0, 1.1, -2.0, 3.5, '0.0', '-0.0', '1.1', '-2.0', '3.5'],
            [0.0, 0.0, 1.1, -2.0, 3.5, 0.0, 0.0, 1.1, -2.0, 3.5],
        ];

        yield [
            'string-array',
            [1, 2, 3, true, false],
            ['1', '2', '3', '1', ''],
        ];

        yield [
            'bool-array',
            ['t' => 1, 'f' => 0],
            ['t' => true, 'f' => false],
        ];
    }

    /**
     * @dataProvider invalidNumericProvider
     *
     * @phpstan-ignore-next-line
     */
    public function testInvalidNumeric(string $prefix, array $envValue, string $expectedMessageFormat): void
    {
        $processor = new ArrayCastEnvVarProcessor();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf($expectedMessageFormat, 'DM'));

        $processor->getEnv($prefix, 'DM', static function () use ($envValue): array {
            return $envValue;
        });
    }

    /**
     * @return \Generator<array{0: string, 1: array, 2: string}>
     */
    public function invalidNumericProvider(): \Generator
    {
        yield [
            'int-array',
            ['0', '1', 'NaN'],
            'Non-numeric member of env var "%s" cannot be cast to int.',
        ];

        yield [
            'float-array',
            ['0.0', '1.0', '1.0.1'],
            'Non-numeric member of env var "%s" cannot be cast to float.',
        ];
    }
}
