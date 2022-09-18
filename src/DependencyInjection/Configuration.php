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
        $rootNode->children()
            ->enumNode('command_provider')
                ->values(['enqueue-php'])
            ->end()
            ->arrayNode('provider_options')
                ->scalarPrototype()->end()
            ->end()
            ->scalarNode('queue_name')
                ->defaultValue('default_queue')
            ->end()
        ->end();

        return $treeBuilder;
    }
}
