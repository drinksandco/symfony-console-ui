<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('console_ui');
        $rootNode = $treeBuilder->getRootNode();
        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-suppress PossiblyUndefinedMethod
         * @psalm-suppress MixedMethodCall
         */
        $rootNode
            ->children()
                ->enumNode('command_provider')
                    ->values(['enqueue_php'])->defaultValue('enqueue_php')
                ->end()
            ->end()
            ->children()
                ->arrayNode('provider_options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('enqueue_php')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('queue_name')->defaultValue('console_queue')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
