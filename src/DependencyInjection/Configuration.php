<?php

declare(strict_types=1);

/*
 * terminal42/rokka-io-api-platform-bridge
 *
 * @copyright  Copyright (c) 2008-2018, terminal42 gmbh
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
        $rootNode = $treeBuilder->root('terminal42_rokka_apiplatform_bridge');

        $rootNode
            ->children()
                ->scalarNode('sourceimage_endpoint')
                    ->defaultValue('/images')
                    ->validate()
                        ->always(function ($sourceImage) {
                            if ('/' !== $sourceImage[0]) {
                                throw new \InvalidArgumentException('The configuration "sourceimage_endpoint" has to start with a "/".');
                            }
                        })
                ->end()
            ->end();

        return $treeBuilder;
    }
}
