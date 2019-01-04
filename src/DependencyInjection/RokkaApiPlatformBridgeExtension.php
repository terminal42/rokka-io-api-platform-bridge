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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Terminal42\RokkaApiPlatformBridge\Controller\RokkaController;

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

        $container->setParameter('terminal42.rokka_apiplatform_bridge.bridge_endpoint', $config['bridge_endpoint']);
        $container->setParameter('terminal42.rokka_apiplatform_bridge.default_organization', $config['default_organization']);
        $container->setParameter('terminal42.rokka_apiplatform_bridge.endpoints', $config['endpoints']);

        $loader->load('services.xml');

        $controllerDef = $container->getDefinition(RokkaController::class);

        // Set API key
        $controllerDef->setArgument(0, $config['api_key']);

        // Set Http Client
        if ($config['http_client']) {
            $controllerDef->setArgument(2, new Reference($config['http_client']));
        }
    }
}
