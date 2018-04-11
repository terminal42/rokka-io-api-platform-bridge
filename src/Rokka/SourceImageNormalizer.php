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

use Rokka\Client\Core\SourceImage;

class SourceImageNormalizer
{
    /**
     * @var string
     */
    private $sourceImageEndpoint;

    public function __construct(string $sourceImageEndpoint)
    {
        $this->sourceImageEndpoint = $sourceImageEndpoint;
    }

    public function normalize(SourceImage $sourceImage): array
    {
        $data = get_object_vars($sourceImage);

        // Remove organization
        unset($data['organization']);

        // Update Link
        $data['link'] = preg_replace(
            '@^/sourceimages/'.preg_quote($sourceImage->organization, '@').'@',
            $this->sourceImageEndpoint,
            $data['link'],
            1
        );

        return $data;
    }
}
