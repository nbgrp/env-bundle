<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace Nbgrp\Tests\EnvBundle;

use Nbgrp\EnvBundle\ArrayCastEnvVarProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @covers \Nbgrp\EnvBundle\ArrayCastEnvVarProcessor
 *
 * @internal
 */
final class ArrayCastEnvVarProcessorTest extends TestCase
{
    /**
     * @dataProvider provideSuccessCases
     */
    public function testSuccess(string $prefix, array $envValue, array $expected): void
    {
        $processor = new ArrayCastEnvVarProcessor();

        self::assertSame($expected, $processor->getEnv($prefix, '', static function () use ($envValue): array {
            return $envValue;
        }));
    }

    /**
     * @return \Generator<array{string, array, array}>
     */
    public function provideSuccessCases(): iterable
    {
        yield 'bool_array' => [
            'bool_array',
            [1, 2, 0, -1, 'true', 'false', 'yes', 'no', 'on', 'off', 'any'],
            [true, true, false, true, true, false, true, false, true, false, false],
        ];

        yield 'int_array' => [
            'int_array',
            ['0', '1', '-2.0', '3.5', 0, 1, -2.0, 3.5],
            [0, 1, -2, 3, 0, 1, -2, 3],
        ];

        yield 'float_array' => [
            'float_array',
            [0.0, -0.0, 1.1, -2.0, 3.5, '0.0', '-0.0', '1.1', '-2.0', '3.5'],
            [0.0, 0.0, 1.1, -2.0, 3.5, 0.0, 0.0, 1.1, -2.0, 3.5],
        ];

        yield 'base64_array' => [
            'base64_array',
            ['ZW5jb2RlZA==', 'ZW5jb2RlZDI'],
            ['encoded', 'encoded2'],
        ];

        yield 'base64url_array' => [
            'base64url_array',
            ['PD9lbmM_Pg==', 'PDw_MT8-Pg'],
            ['<?enc?>', '<<?1?>>'],
        ];

        yield 'string_array' => [
            'string_array',
            [1, 2, 3, true, false],
            ['1', '2', '3', '1', ''],
        ];

        yield 'bool_array with keys' => [
            'bool_array',
            ['t' => 1, 'f' => 0],
            ['t' => true, 'f' => false],
        ];
    }

    /**
     * @dataProvider provideInvalidNumericCases
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
     * @return \Generator<array{string, array, string}>
     */
    public function provideInvalidNumericCases(): iterable
    {
        yield [
            'int_array',
            ['0', '1', 'NaN'],
            'Non-numeric member of environment variable "%s" cannot be cast to int.',
        ];

        yield [
            'float_array',
            ['0.0', '1.0', '1.0.1'],
            'Non-numeric member of environment variable "%s" cannot be cast to float.',
        ];
    }

    /**
     * @dataProvider provideInvalidBase64Cases
     */
    public function testInvalidBase64(string $prefix, array $envValue, string $expectedMessageFormat): void
    {
        $processor = new ArrayCastEnvVarProcessor();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf($expectedMessageFormat, 'DM'));

        $processor->getEnv($prefix, 'DM', static function () use ($envValue): array {
            return $envValue;
        });
    }

    /**
     * @return \Generator<array{string, array, string}>
     */
    public function provideInvalidBase64Cases(): iterable
    {
        yield [
            'base64_array',
            ['Z!==', 'Z!'],
            'Environment variable "%s" must be a valid base64 string.',
        ];

        yield [
            'base64url_array',
            ['-=', '_!'],
            'Environment variable "%s" must be a valid base64url string.',
        ];
    }
}
