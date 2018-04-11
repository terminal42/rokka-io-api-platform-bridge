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

namespace Terminal42\RokkaApiPlatformBridge\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Rokka\Client\Core\SourceImage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Terminal42\RokkaApiPlatformBridge\Entity\Image;
use Terminal42\RokkaApiPlatformBridge\Rokka\SourceImageNormalizer;

class PostReadAndWriteSourceImageListener implements EventSubscriberInterface
{
    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;
    /**
     * @var SourceImageNormalizer
     */
    private $sourceImageNormalizer;

    public function __construct(RequestMatcherInterface $requestMatcher, SourceImageNormalizer $sourceImageNormalizer)
    {
        $this->requestMatcher = $requestMatcher;
        $this->sourceImageNormalizer = $sourceImageNormalizer;
    }

    public function onPostRead(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requestMatcher->matches($request)) {
            return;
        }

        if ('GET' !== $request->getMethod()) {
            return;
        }

        /** @var SourceImage $image */
        $image = $request->attributes->get('data');

        $request->attributes->set('data',
            $this->sourceImageNormalizer->normalize($image)
        );
    }

    public function onPostWrite(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requestMatcher->matches($request)) {
            return;
        }

        if ('POST' !== $request->getMethod()) {
            return;
        }

        /** @var Image $image */
        $image = $event->getControllerResult();

        $event->setControllerResult(
            $this->sourceImageNormalizer->normalize($image->getSourceImage())
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onPostRead', EventPriorities::POST_READ],
            KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE],
        ];
    }
}
