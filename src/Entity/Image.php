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

namespace Terminal42\RokkaApiPlatformBridge\Entity;

use Rokka\Client\Core\SourceImage;

class Image
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var array
     */
    private $file;

    /**
     * @var array
     */
    private $meta_user;

    /**
     * @var array
     */
    private $meta_dynamic;

    /**
     * @var SourceImage
     */
    private $sourceImage;

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     *
     * @return Image
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return array
     */
    public function getFile(): array
    {
        return $this->file;
    }

    /**
     * @param array $file
     *
     * @return Image
     */
    public function setFile(array $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getMetaUser(): ?array
    {
        return $this->meta_user;
    }

    public function setMetaUser(?array $meta_user): self
    {
        $this->meta_user = $meta_user;

        return $this;
    }

    public function getMetaDynamic(): ?array
    {
        return $this->meta_dynamic;
    }

    public function setMetaDynamic(?array $meta_dynamic): self
    {
        $this->meta_dynamic = $meta_dynamic;

        return $this;
    }

    public function getSourceImage(): ?SourceImage
    {
        return $this->sourceImage;
    }

    public function setSourceImage(?SourceImage $sourceImage): self
    {
        $this->sourceImage = $sourceImage;

        return $this;
    }
}
