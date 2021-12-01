<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace Nbgrp\Tests\EnvBundle\DependencyInjection;

use Nbgrp\EnvBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Nbgrp\EnvBundle\DependencyInjection\Configuration
 *
 * @internal
 */
final class ConfigurationTest extends TestCase
{
    /** @var Processor */
    private $processor;

    /**
     * @dataProvider validConfigProvider
     */
    public function testValidConfig(array $config, array $expected, string $description): void
    {
        self::assertSame($expected, $this->processor->processConfiguration(new Configuration(), [$config]), $description);
    }

    /**
     * @return \Generator<array{array, array, string}>
     */
    public function validConfigProvider(): \Generator
    {
        yield [
            [],
            [
                'array_cast' => [
                    'enabled' => false,
                ],
                'csv' => [
                    'enabled' => false,
                    'delimiters' => [],
                ],
            ],
            'All processors should be disabled by default.',
        ];

        yield [
            [
                'array_cast' => null,
            ],
            [
                'array_cast' => [
                    'enabled' => true,
                ],
                'csv' => [
                    'enabled' => false,
                    'delimiters' => [],
                ],
            ],
            'ArrayCast should be enabled.',
        ];

        yield [
            [
                'array_cast' => null,
                'csv' => [
                    'dot' => '.',
                ],
            ],
            [
                'array_cast' => ['enabled' => true],
                'csv' => [
                    'enabled' => true,
                    'delimiters' => [
                        'dot' => '.',
                    ],
                ],
            ],
            'ArrayCast and CSV should be enabled (CSV with dot delimiter).',
        ];

        yield [
            [
                'array_cast' => false,
                'csv' => [
                    'dot' => '.',
                    'dash' => '-',
                ],
            ],
            [
                'array_cast' => ['enabled' => false],
                'csv' => [
                    'enabled' => true,
                    'delimiters' => [
                        'dot' => '.',
                        'dash' => '-',
                    ],
                ],
            ],
            'CSV should be enabled with a few delimiters.',
        ];
    }

    /**
     * @dataProvider invalidConfigProvider
     */
    public function testInvalidConfig(array $config, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->processor->processConfiguration(new Configuration(), [$config]);
    }

    /**
     * @return \Generator<array{array, string}>
     */
    public function invalidConfigProvider(): \Generator
    {
        yield [
            [
                'unknown_processor' => null,
            ],
            'Unrecognized option "unknown_processor"',
        ];

        yield [
            [
                'csv' => [
                    'multichar' => '++',
                ],
            ],
            'Delimiter should be one character only.',
        ];
    }

    protected function setUp(): void
    {
        $this->processor = new Processor();
    }
}
