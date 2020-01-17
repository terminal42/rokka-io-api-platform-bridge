<?php

declare(strict_types=1);

/*
 * terminal42/rokka-io-api-platform-bridge
 *
 * @copyright  Copyright (c) 2008-2020, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/rokka-io-api-platform-bridge
 */

namespace Terminal42\RokkaApiPlatformBridge\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Terminal42\RokkaApiPlatformBridge\Controller\RokkaController;

class RokkaLoader extends Loader
{
    /**
     * @var string
     */
    private $bridgeEndpoint;

    /**
     * @var array
     */
    private $endpoints;

    /**
     * @var string
     */
    private $defaultOrganization;

    public function __construct(string $bridgeEndpoint, array $endpoints, string $defaultOrganization = null)
    {
        $this->bridgeEndpoint = $bridgeEndpoint;
        $this->endpoints = $endpoints;
        $this->defaultOrganization = $defaultOrganization;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        foreach ($this->endpoints as $originalPath => $endpoint) {
            $path = $originalPath;

            if (null !== $this->defaultOrganization) {
                $path = str_replace('/{organization}', '', $path);
            }

            $route = new Route($path);
            $route->setMethods($endpoint['methods']);
            $route->setDefault('_controller', RokkaController::class);
            $route->setDefault('_rokka_original_path', $originalPath);

            if (null !== $this->defaultOrganization) {
                $route->setDefault('organization', $this->defaultOrganization);
            }

            $routes->add('rokka_api_platform_bridge_'.$path, $route);
        }

        $routes->addPrefix($this->bridgeEndpoint);

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'rokka_api_platform_bridge' === $type;
    }
}
