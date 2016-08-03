<?php

namespace C2is\DoctrineCacheInvalidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('c2is_doctrine_cache_invalidation');

        $rootNode->children()
            ->booleanNode('enabled')
                ->defaultFalse()
            ->end()
            ->enumNode('type')
                ->defaultValue('annotation')
                ->values(['annotation', 'yml'])
            ->end()
            ->scalarNode('yml_file')->defaultNull()->end()
            ->enumNode('driver')
                ->defaultValue('default')
                ->values(['default', 'gedmo', 'custom'])
            ->end()
            ->scalarNode('custom_driver_id')->defaultNull()->end()
            ->enumNode('doctrine_cache_driver_id')
                ->defaultValue('array')
                ->values(['array', 'predis'])
            ->end()
            ->arrayNode('cache_driver_options')
                ->children()
                    ->scalarNode('host')->defaultNull()->end()
                    ->scalarNode('port')->defaultNull()->end()
                    ->scalarNode('database')->defaultNull()->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
