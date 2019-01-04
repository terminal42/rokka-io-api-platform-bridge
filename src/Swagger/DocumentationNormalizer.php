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

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var NormalizerInterface
     */
    private $decorated;

    /**
     * @var Extractor
     */
    private $extractor;

    public function __construct(NormalizerInterface $decorated, Extractor $extractor)
    {
        $this->decorated = $decorated;
        $this->extractor = $extractor;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = (array) $this->decorated->normalize($object, $format, $context);

        $this->extractor->mergeWithExistingSwaggerDocs($docs);

        return $docs;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return $this->decorated instanceof CacheableSupportsMethodInterface && $this->decorated->hasCacheableSupportsMethod();
    }
}
