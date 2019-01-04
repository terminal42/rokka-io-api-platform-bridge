<?php

declare(strict_types=1);

/*
 * terminal42/rokka-io-api-platform-bridge
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/rokka-io-api-platform-bridge
 */

namespace Terminal42\RokkaApiPlatformBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rokka_api_platform_bridge');

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                ->end()
                ->scalarNode('bridge_endpoint')
                    ->defaultValue('/rokka')
                    ->validate()
                        ->ifTrue(function ($bridgeEndpoint) {
                            return '/' !== $bridgeEndpoint[0];
                        })
                        ->thenInvalid('The configuration value of "bridge_endpoint" must start with a "/".')
                    ->end()
                ->end()
                ->scalarNode('default_organization')
                    ->defaultNull()
                ->end()
                ->scalarNode('http_client')
                    ->defaultNull()
                ->end()
                ->arrayNode('endpoints')
                    ->useAttributeAsKey('path')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')
                            ->end()
                            ->arrayNode('methods')
                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()
                                ->scalarPrototype()
                                ->validate()
                                    ->ifNotInArray(['GET', 'PUT', 'DELETE', 'POST', 'PATCH'])
                                    ->thenInvalid('Invalid method "%s"')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
