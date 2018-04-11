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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RokkaApiPlatformBridgeExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.xml');
        $loader->load('meta.xml');

        $definition = $container->getDefinition('terminal42.rokka_apiplatform_bridge.sourceimages_requestmatcher');
        $definition->setArgument(0, $config['sourceimage_endpoint']);
        $definition = $container->getDefinition('api_platform.metadata.extractor.terminal42_rokka_image');
        $definition->setArgument(0, $config['sourceimage_endpoint']);
        $definition = $container->getDefinition('Terminal42\RokkaApiPlatformBridge\Rokka\SourceImageNormalizer');
        $definition->setArgument(0, $config['sourceimage_endpoint']);
    }
}
