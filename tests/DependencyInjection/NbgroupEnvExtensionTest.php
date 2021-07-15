<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace NbGroup\Tests\Symfony\DependencyInjection;

use NbGroup\Symfony\DependencyInjection\NbgroupEnvExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \NbGroup\Symfony\DependencyInjection\NbgroupEnvExtension
 *
 * @internal
 */
final class NbgroupEnvExtensionTest extends TestCase
{
    public function testComplexUsage(): void
    {
        $container = new ContainerBuilder();
        $extension = new NbgroupEnvExtension();

        $extension->load([
            'nbgroup_env' => [
                'array_cast' => null,
                'csv' => [
                    'bar' => '|',
                    'colon' => ':',
                    'dash' => '-',
                    'dot' => '.',
                ],
            ],
        ], $container);

        $container->setParameter('env(BAR_SEPARATED_ENV)', '42|45|"1|2"');
        $container->setParameter('env(COLON_SEPARATED_ENV)', '0:yes:false');
        $container->setParameter('env(DASH_SEPARATED_ENV)', '42-"-45"-"-12.2"-"-0.314e-1"');
        $container->setParameter('env(DOT_SEPARATED_ENV)', '1."2.2"."-3.3".-0');
        $container->setParameter('env(JSON_FLOAT_ENV)', '{"key1": "1.1", "key2": 0.3e1}');
        $container->setParameter('env(JSON_STRING_ENV)', '["foo", "foo \"bar\"", ""]');
        $container->setParameter('env(COMMA_SEPARATED_ENV)', '"1",2e2,"-0"');

        $container->setParameter('strings', '%env(string-array:csv-bar:BAR_SEPARATED_ENV)%');
        $container->setParameter('bools', '%env(bool-array:csv-colon:COLON_SEPARATED_ENV)%');
        $container->setParameter('floats', '%env(float-array:csv-dash:DASH_SEPARATED_ENV)%');
        $container->setParameter('ints', '%env(int-array:csv-dot:DOT_SEPARATED_ENV)%');
        $container->setParameter('floats_json', '%env(float-array:json:JSON_FLOAT_ENV)%');
        $container->setParameter('strings_json', '%env(string-array:json:JSON_STRING_ENV)%');
        $container->setParameter('ints_csv', '%env(int-array:csv:COMMA_SEPARATED_ENV)%');

        $container->compile(true);

        self::assertSame(['42', '45', '1|2'], $container->getParameter('strings'), 'strings');
        self::assertSame([false, true, false], $container->getParameter('bools'), 'bools');
        self::assertSame([42.0, -45.0, -12.2, -0.0314], $container->getParameter('floats'), 'floats');
        self::assertSame([1, 2, -3, 0], $container->getParameter('ints'), 'ints');
        self::assertSame(['key1' => 1.1, 'key2' => 3.0], $container->getParameter('floats_json'), 'floats_json');
        self::assertSame(['foo', 'foo "bar"', ''], $container->getParameter('strings_json'), 'strings_json');
        self::assertSame([1, 200, 0], $container->getParameter('ints_csv'), 'ints_csv');
    }
}
