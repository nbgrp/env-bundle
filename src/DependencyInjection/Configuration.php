<?php declare(strict_types=1);
// SPDX-License-Identifier: BSD-3-Clause

namespace NbGroup\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @final
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nbgroup_env');
        $rootNode = $treeBuilder->getRootNode();

        // @formatter:off
        /** @phpstan-ignore-next-line */
        $rootNode
            ->info('nb:group environment variable processors configuration')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->addArrayCastSection())
                ->append($this->addCsvSection())
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }

    private function addArrayCastSection(): ArrayNodeDefinition
    {
        $rootNode = new NodeBuilder();

        // @formatter:off
        return $rootNode->arrayNode('array_cast')
            ->canBeEnabled()
        ;
        // @formatter:on
    }

    private function addCsvSection(): ArrayNodeDefinition
    {
        $rootNode = new NodeBuilder();

        // @formatter:off
        /** @phpstan-ignore-next-line */
        return $rootNode->arrayNode('csv')
            ->canBeEnabled()
            ->ignoreExtraKeys(false)
            ->beforeNormalization()
                ->ifArray()
                ->then(static function (array $value): array {
                    return array_intersect_key(
                        array_merge(
                            $value,
                            [
                                'delimiters' => array_diff_key(
                                    $value['delimiters'] ?? $value,
                                    ['enabled' => null]
                                ),
                            ]
                        ),
                        ['enabled' => null, 'delimiters' => null]
                    );
                })
            ->end()
            ->fixXmlConfig('delimiter')
            ->children()
                ->arrayNode('delimiters')
                    ->info('Delimiter name used as part of env var prefix (prefixed with "csv-") and value as a separator.')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(static function ($value): bool {
                                return \strlen($value) > 1;
                            })
                            ->thenInvalid('Delimiter should be one character only.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // @formatter:on
    }
}
