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

namespace Terminal42\RokkaApiPlatformBridge\Swagger;

use Http\Client\Exception;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Cache\CacheItemPoolInterface;

class Extractor
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

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

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(CacheItemPoolInterface $cacheItemPool, string $bridgeEndpoint, array $endpoints, string $defaultOrganization = null, HttpClient $client = null, MessageFactory $messageFactory = null)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->bridgeEndpoint = $bridgeEndpoint;
        $this->endpoints = $endpoints;
        $this->defaultOrganization = $defaultOrganization;

        $this->client = $client ?? HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?? MessageFactoryDiscovery::find();
    }

    public function mergeWithExistingSwaggerDocs(array &$docs)
    {
        // Only supports Swagger 2.0
        if (!isset($docs['swagger']) || '2.0' !== $docs['swagger']) {
            return;
        }

        $cacheItem = $this->cacheItemPool->getItem('terminal42.rokka_apiplatform_bridge.swagger_doc');

        if ($cacheItem->isHit()) {
            $swaggerDocs = $cacheItem->get();
        } else {
            try {
                $response = $this->client->sendRequest(
                    $this->messageFactory->createRequest('GET', 'https://api.rokka.io/doc.json')
                );

                $swaggerDocs = $response->getBody()->getContents();
            } catch (Exception | \Exception $e) {
                return;
            }
        }

        $swaggerDocs = json_decode($swaggerDocs, true);

        foreach ($swaggerDocs['paths'] as $path => $methods) {
            foreach ($methods as $method => $config) {
                if (!isset($this->endpoints[$path]) || !\in_array(strtoupper($method), $this->endpoints[$path]['methods'], true)) {
                    continue;
                }

                // Override path
                $path = $this->bridgeEndpoint.$path;

                if (null !== $this->defaultOrganization) {
                    $path = str_replace('/{organization}', '', $path);
                }

                // Remove the organization parameter in case we have a default
                foreach ((array) $config['parameters'] as $k => $parameter) {
                    if ('organization' === $parameter['name']) {
                        if (null !== $this->defaultOrganization) {
                            unset($config['parameters'][$k]);
                        }
                    }
                }

                // Override tags
                $config['tags'] = ['rokka.io'];

                // Replace refs
                $config = $this->recursiveReplaceRefs($config);

                $docs['paths'][$path][$method] = $config;
            }
        }

        // Add definitions
        foreach ($swaggerDocs['definitions'] as $k => $definition) {
            $docs['definitions']['rokka_'.$k] = $definition;
        }
    }

    private function recursiveReplaceRefs(array $docs)
    {
        foreach ($docs as $k => $v) {
            if ('$ref' === $k) {
                $docs[$k] = str_replace('#/definitions/', '#/definitions/rokka_', $v);
            }

            if (\is_array($v)) {
                $docs[$k] = $this->recursiveReplaceRefs($v);
            }
        }

        return $docs;
    }
}
