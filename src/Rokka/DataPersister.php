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

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use GuzzleHttp\Exception\GuzzleException;
use Rokka\Client\Image as ImageApi;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Terminal42\RokkaApiPlatformBridge\Entity\Image;
use Terminal42\RokkaApiPlatformBridge\Exception\InvalidFileDataException;

class DataPersister implements DataPersisterInterface
{
    /**
     * @var ImageApi
     */
    private $imageApi;

    /**
     * DataPersister constructor.
     *
     * @param ImageApi $imageApi
     */
    public function __construct(ImageApi $imageApi)
    {
        $this->imageApi = $imageApi;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof Image;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($data)
    {
        /* @var $data Image */
        if (!$this->supports($data)) {
            throw new \InvalidArgumentException('Data to be persisted must be an instance of '.Image::class);
        }

        $fileContent = base64_decode($data->getFile()['content'], true);

        if (false === $fileContent || !isset($data->getFile()['name'])) {
            throw new InvalidFileDataException();
        }

        $fileName = $data->getFile()['name'];

        $options['meta_user'] = $data->getMetaUser() ?: [];
        $options['meta_dynamic'] = $data->getMetaDynamic() ?: [];

        try {
            $collection = $this->imageApi->uploadSourceImage(
                $fileContent,
                $fileName,
                '',
                $options
            );
            $data->setHash($collection->current()->hash);
            $data->setSourceImage($collection->current());
        } catch (GuzzleException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data)
    {
        if (!$this->supports($data)) {
            throw new \InvalidArgumentException('Data to be removed must be an instance of '.Image::class);
        }

        /* @var $data Image */
        try {
            $this->imageApi->deleteSourceImage($data->getHash());
        } catch (GuzzleException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
