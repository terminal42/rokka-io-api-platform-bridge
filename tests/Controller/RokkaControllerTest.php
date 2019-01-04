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

namespace Terminal42\RokkaApiPlatformBridge\Test\Controller;

use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\RokkaApiPlatformBridge\Controller\RokkaController;
use Zend\Diactoros\Response as Psr7Response;
use Zend\Diactoros\ServerRequest;

class RokkaControllerTest extends TestCase
{
    /**
     * @dataProvider controllerProvider
     */
    public function testController(Request $request, callable $expectedRequestValidator, ResponseInterface $mockedResponse, Response $expectedResponse)
    {
        $client = $this->createMock(HttpClient::class);
        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback($expectedRequestValidator))
            ->willReturn($mockedResponse);

        $controller = new RokkaController('api-key', '/images', $client);

        $this->assertSame(serialize($controller($request, 'foobar-organization')), serialize($expectedResponse)); // csfixer turns assertEquals() into assertSame()
        //$this->assertEquals($controller($request, 'foobar-organization'), $expectedResponse);
    }

    public function controllerProvider()
    {
        yield 'Test creating a new source image' => [
            $this->createRequest('/images/sourceimages', '/sourceimages/{organization}', 'foobar-organization', 'POST', [], 'filedata'),
            function (ServerRequest $request) {
                $this->assertSame([
                    'api-version' => ['1'],
                    'api-key' => ['api-key'],
                    'content-length' => ['95'],
                    'Host' => ['api.rokka.io'],
                ], $request->getHeaders());

                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('https://api.rokka.io/sourceimages/foobar-organization', (string) $request->getUri());
                $this->assertArrayHasKey('filedata', $request->getUploadedFiles());

                /** @var \Zend\Diactoros\UploadedFile $upload */
                $upload = $request->getUploadedFiles()['filedata'];

                $this->assertSame('pixel.png', $upload->getClientFilename());
                $this->assertSame('image/png', $upload->getClientMediaType());
                $this->assertSame(95, $upload->getSize());

                return true;
            },
            new Psr7Response(),
            (new Response())->setProtocolVersion('1.1'),
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
