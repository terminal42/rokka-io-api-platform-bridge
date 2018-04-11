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

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use GuzzleHttp\Exception\GuzzleException;

class ItemDataProvider extends AbstractDataProvider implements ItemDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        try {
            return $this->imageApi->getSourceImage($id);
        } catch (GuzzleException $e) {
            return null;
        }
    }
}
