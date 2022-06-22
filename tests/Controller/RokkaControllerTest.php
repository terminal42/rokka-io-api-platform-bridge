<?php

declare(strict_types=1);

/*
 * terminal42/rokka-io-api-platform-bridge
 *
 * @copyright  Copyright (c) 2008-2022, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/rokka-io-api-platform-bridge
 */

namespace Terminal42\RokkaApiPlatformBridge\Test\Controller;

use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\RokkaApiPlatformBridge\Controller\RokkaController;

class RokkaControllerTest extends TestCase
{
    /**
     * @dataProvider controllerProvider
     */
    public function testController(Request $request, callable $expectedRequestValidator, ResponseInterface $mockedResponse, Response $expectedResponse): void
    {
        $client = $this->createMock(HttpClient::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback($expectedRequestValidator))
            ->willReturn($mockedResponse);

        $controller = new RokkaController('api-key', '/images', $client);

        $response = $controller($request, 'foobar-organization');

        // Validate the most important things on the response
        $this->assertSame($expectedResponse->getStatusCode(), $response->getStatusCode());
        $this->assertSame($expectedResponse->headers->all(), $response->headers->all());
        $this->assertSame($expectedResponse->getContent(), $response->getContent());
    }

    public function controllerProvider()
    {
        yield 'Test creating a new source image' => [
            $this->createRequest('/images/sourceimages', '/sourceimages/{organization}', 'foobar-organization', 'POST', ['Content-Type' => 'multipart/form-data; charset=utf-8; boundary=foobar', 'Foobar' => 'We do not care about this'], 'filedata'),
            function (RequestInterface $request) {
                // Make sure we only have the headers we want to
                $this->assertCount(6, array_keys($request->getHeaders()));

                // Assert header contents
                $this->assertSame('1', $request->getHeaderLine('api-version'));
                $this->assertSame('api-key', $request->getHeaderLine('api-key'));
                $this->assertSame('api.rokka.io', $request->getHeaderLine('Host'));

                $this->assertStringContainsString('multipart/form-data; charset=utf-8; boundary=', $request->getHeaderLine('Content-Type'));
                $this->assertStringNotContainsString('multipart/form-data; charset=utf-8; boundary=foobar', $request->getHeaderLine('Content-Type'));

                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('https://api.rokka.io/sourceimages/foobar-organization', (string) $request->getUri());

                $body = (string) $request->getBody();

                $this->assertStringContainsString('Content-Disposition: form-data; name="pixel.png"; filename="pixel.png"', $body);
                $this->assertStringContainsString('Content-Length: 95', $body);
                $this->assertStringContainsString('Content-Type: image/png', $body);

                return true;
            },
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json', 'Content-Length' => 579, 'Transfer-Encoding' => 'chunked', 'Keep-Alive' => 'timeout=5, max=1000'], json_encode(json_decode('{
                "total": "1",
                "items": [{
                    "hash": "54e3938e63191e119d7bd9404dec6e44be469bda",
                    "short_hash": "54e393",
                    "binary_hash": "37ebc95296615ac24ebebd7fcc35ebce4f8a7582",
                    "created": "2019-01-04T12:15:26+00:00",
                    "name": "phpXszLs3",
                    "mimetype": "image\/png",
                    "format": "png",
                    "size": "85845",
                    "width": "300",
                    "height": "300",
                    "organization": "foobar-organization",
                    "link": "\/sourceimages\/foobar-organization\/37ebc95296615ac24ebebd7fcc35ebce4f8a7582",
                    "deleted": ""
                }]
            }'))),
            new Response(json_encode(json_decode('{
                "total": "1",
                "items": [{
                    "hash": "54e3938e63191e119d7bd9404dec6e44be469bda",
                    "short_hash": "54e393",
                    "binary_hash": "37ebc95296615ac24ebebd7fcc35ebce4f8a7582",
                    "created": "2019-01-04T12:15:26+00:00",
                    "name": "phpXszLs3",
                    "mimetype": "image\/png",
                    "format": "png",
                    "size": "85845",
                    "width": "300",
                    "height": "300",
                    "organization": "foobar-organization",
                    "link": "\/images\/sourceimages\/foobar-organization\/37ebc95296615ac24ebebd7fcc35ebce4f8a7582",
                    "deleted": ""
                }]
            }')), 200, ['Content-Type' => 'application/json', 'Content-Length' => '439']),
        ];

        yield 'Invalid request' => [
            $this->createRequest('/images/sourceimages', '/sourceimages/{organization}', 'foobar-organization', 'POST', [], 'filedata'),
            function () { return true; },
            new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json', 'Content-Length' => 60], json_encode(json_decode('{
                "code": "400",
                "message": "Something went wrong"
            }'))),
            new Response(json_encode(json_decode('{
                "code": "400",
                "message": "Something went wrong"
            }')), 400, ['Content-Type' => 'application/json', 'Content-Length' => '47']),
        ];
    }

    private function createRequest(string $uri, string $originalPath, string $organization, string $method, array $headers = [], string $uploadKey = null): Request
    {
        $request = Request::create($uri, $method);
        $request->headers->add($headers);

        if ($uploadKey) {
            $request->files->set($uploadKey, new UploadedFile(__DIR__.'/../Fixtures/pixel.png', 'pixel.png', 'image/png', null, true));
        }

        $request->attributes->set('_controller', RokkaController::class);
        $request->attributes->set('_route_params', [
            '_rokka_original_path' => $originalPath,
            'organization' => $organization,
        ]);

        return $request;
    }
}
