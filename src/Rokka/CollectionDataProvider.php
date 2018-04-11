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

namespace Terminal42\RokkaApiPlatformBridge\Rokka;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CollectionDataProvider extends AbstractDataProvider implements CollectionDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new BadRequestHttpException('No request available.');
        }

        try {
            return $this->imageApi->searchSourceImages(
                $request->query->get('search', []),
                explode(',', $request->query->get('sort', '')),
                $request->query->getInt('limit'),
                $request->query->getInt('offset')
            );
        } catch (GuzzleException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
