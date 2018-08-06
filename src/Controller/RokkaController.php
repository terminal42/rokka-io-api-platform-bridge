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

namespace Terminal42\RokkaApiPlatformBridge\Controller;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\Uri;

class RokkaController
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $bridgeEndpoint;

    /**
     * @var string
     */
    private $defaultOrganization;

    /**
     * @var HttpClient
     */
    private $client;

    public function __construct(string $apiKey, string $bridgeEndpoint, string $defaultOrganization = null, HttpClient $client = null)
    {
        $this->apiKey = $apiKey;
        $this->bridgeEndpoint = $bridgeEndpoint;
        $this->defaultOrganization = $defaultOrganization;
        $this->client = $client ?? HttpClientDiscovery::find();
    }

    /**
     * Mirrors the request to rokka.io.
     */
    public function __invoke(Request $request, string $organization): Response
    {
        $psr7Factory = new DiactorosFactory();
        $httpFoundationFactory = new HttpFoundationFactory();

        // Remove personal headers
        $request->cookies = new ParameterBag();
        $request->headers->remove('Authorization');

        // Add API key header
        $request->headers->set('Api-Key', $this->apiKey);

        // Ensure content-length is present
        if (!$request->headers->has('Content-Length')) {
            $request->headers->set('Content-Length', \strlen($request->getContent()));
        }

        $psrRequest = $psr7Factory->createRequest($request);

        // Override URI
        $rokkaPath = $this->createRokkaPath($request);
        $psrRequest = $psrRequest->withUri(new Uri('https://api.rokka.io'.$rokkaPath));

        // Send the request
        $psrResponse = $this->client->sendRequest($psrRequest);

        $response = $httpFoundationFactory->createResponse($psrResponse);

        // Normalize response
        $response = $this->normalizeResponse($response, $rokkaPath);

        return $response;
    }

    /**
     * Normalizes the response, meaning it searches for links and automatically prefixes them with the bridge
     * endpoint so for the end user this feels like a natural API endpoint.
     */
    private function normalizeResponse(Response $response, string $rokkaPath): Response
    {
        if ('application/json' === $response->headers->get('Content-Type')) {
            $content = json_decode($response->getContent(), true);
            $content = $this->recursiveReplaceLinks($content, $rokkaPath);
            $response = new JsonResponse($content);
        }

        return $response;
    }

    private function recursiveReplaceLinks(array $content, string $rokkaPath): array
    {
        foreach ($content as $k => $v) {
            if (\is_array($v)) {
                $content[$k] = $this->recursiveReplaceLinks($v, $rokkaPath);
            } else {
                $content[$k] = preg_replace('@^'.preg_quote($rokkaPath, '@').'@',
                    $this->bridgeEndpoint.$rokkaPath,
                    $v
                );
            }
        }

        return $content;
    }

    private function createRokkaPath(Request $request): string
    {
        $path = $request->attributes->get('_route_params')['_rokka_original_path'];

        foreach ($request->attributes->get('_route_params') as $param => $value) {
            $path = str_replace('{'.$param.'}', $value, $path);
        }

        return $path;
    }
}
