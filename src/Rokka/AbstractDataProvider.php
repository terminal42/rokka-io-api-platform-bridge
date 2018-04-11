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

use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Rokka\Client\Image;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractDataProvider implements RestrictedDataProviderInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Image
     */
    protected $imageApi;
    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    public function __construct(RequestMatcherInterface $requestMatcher, RequestStack $requestStack, Image $imageApi)
    {
        $this->requestMatcher = $requestMatcher;
        $this->requestStack = $requestStack;
        $this->imageApi = $imageApi;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $this->requestMatcher->matches($this->requestStack->getCurrentRequest());
    }
}
