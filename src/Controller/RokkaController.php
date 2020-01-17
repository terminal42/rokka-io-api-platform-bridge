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

namespace Terminal42\RokkaApiPlatformBridge\Controller;

use GuzzleHttp\Psr7\MultipartStream;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\HeaderBag;
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
     * @var HttpClient
     */
    private $client;

    public function __construct(string $apiKey, string $bridgeEndpoint, HttpClient $client = null)
    {
        $this->apiKey = $apiKey;
        $this->bridgeEndpoint = $bridgeEndpoint;
        $this->client = $client ?? HttpClientDiscovery::find();
    }

    /**
     * Mirrors the request to rokka.io.
     */
    public function __invoke(Request $request, string $organization): Response
    {
        // Clone the request because we're going to adjust it for our purposes but we don't want to affect
        // the original one
        $request = clone $request;

        $psr7Factory = new DiactorosFactory();
        $httpFoundationFactory = new HttpFoundationFactory();

        // Extract rokka.io path from attributes
        $rokkaPath = $this->createRokkaPath($request);

        // Normalize request
        $this->normalizeRequest($request);

        $psrRequest = $psr7Factory->createRequest($request);

        // Prepare PSR-7 request
        $this->prepareRequest($psrRequest, $rokkaPath);

        // Send the request
        $psrResponse = $this->client->sendRequest($psrRequest);

        $response = $httpFoundationFactory->createResponse($psrResponse);

        // Normalize response
        $this->normalizeResponse($response, $rokkaPath);

        return $response;
    }

    private function prepareRequest(ServerRequestInterface &$psrRequest, string $rokkaPath): void
    {
        // Override URI
        $psrRequest = $psrRequest->withUri(new Uri('https://api.rokka.io'.$rokkaPath));

        // Set body if we had file uploads
        /** @var UploadedFileInterface[] $uploadedFiles */
        $uploadedFiles = $psrRequest->getUploadedFiles();

        if (0 === \count($uploadedFiles)) {
            return;
        }

        foreach ($uploadedFiles as $file) {
            $multipartElements[] = [
                'name' => $file->getClientFilename(),
                'contents' => $file->getStream(),
            ];
        }

        $multipart = new MultipartStream($multipartElements);

        $psrRequest = $psrRequest->withHeader('Content-Type', 'multipart/form-data; charset=utf-8; boundary='.$multipart->getBoundary());
        $psrRequest = $psrRequest->withBody($multipart);
    }

    /**
     * Normalizes the request in a way it does not expose useless information from the client to the
     * rokka.io servers (e.g. authentication details) and provides the api key.
     */
    private function normalizeRequest(Request $request): void
    {
        // Remove cookies
        $request->cookies = new ParameterBag();

        // Only allow whitelisted headers
        $headerWhitelist = [
            'user-agent',
            'accept',
            'content-type',
            'content-length',
        ];

        foreach (array_keys($request->headers->all()) as $header) {
            if (!\in_array($header, $headerWhitelist, true)) {
                $request->headers->remove($header);
            }
        }

        // Add API headers
        $request->headers->set('api-version', '1');
        $request->headers->set('api-key', $this->apiKey);

        // Remove attributes
        $request->attributes = new ParameterBag();
    }

    private function removeHopByHopHeaders(HeaderBag $headerBag): void
    {
        $hopByHopHeaders = [
            'connection',
            'keep-alive',
            'proxy-authenticate',
            'proxy-authorization',
            'te',
            'trailer',
            'transfer-encoding',
            'upgrade',
        ];

        foreach ($hopByHopHeaders as $header) {
            $headerBag->remove($header);
        }
    }

    /**
     * Normalizes the response, meaning it searches for links and automatically prefixes them with the bridge
     * endpoint so for the end user this feels like a natural API endpoint.
     */
    private function normalizeResponse(Response &$response, string $rokkaPath): void
    {
        $this->removeHopByHopHeaders($response->headers);

        if ('application/json' === $response->headers->get('Content-Type')) {
            $content = json_decode($response->getContent(), true);
            $content = $this->recursiveReplaceLinks($content, $rokkaPath);
            $response = new JsonResponse($content, $response->getStatusCode(), $response->headers->all());

            // Update Content-Length header
            $response->headers->set('Content-Length', \strlen($response->getContent()));
        }
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
