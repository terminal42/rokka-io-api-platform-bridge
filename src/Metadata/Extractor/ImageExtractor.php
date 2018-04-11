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

namespace Terminal42\RokkaApiPlatformBridge\Metadata\Extractor;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Metadata\Extractor\ExtractorInterface;
use Doctrine\Common\Inflector\Inflector;
use Terminal42\RokkaApiPlatformBridge\Entity\Image;

class ImageExtractor implements ExtractorInterface
{
    /**
     * @var string
     */
    private $sourceImageEndpoint;

    /**
     * @param string $sourceImageEndpoint
     */
    public function __construct(string $sourceImageEndpoint)
    {
        $this->sourceImageEndpoint = $sourceImageEndpoint;
    }

    /**
     * Parses all metadata files and convert them in an array.
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function getResources(): array
    {
        $resourceClass = Image::class;
        $endpointPluralName = preg_replace('@^/@', '', $this->sourceImageEndpoint, 1);
        $endpointSingularName = Inflector::singularize($endpointPluralName);
        $shortName = Inflector::ucwords($endpointSingularName);

        // TODO: Limit to application/json once https://github.com/api-platform/core/pull/1798 is merged

        return [$resourceClass => [
            'shortName' => $shortName,
            'description' => 'Represents an image resource',
            'iri' => $resourceClass,
            'itemOperations' => $this->getItemOperations($endpointPluralName),
            'collectionOperations' => $this->getCollectionOperations($endpointPluralName),
            'subresourceOperations' => null,
            'graphql' => null,
            'attributes' => $this->getAttributes(),
            'properties' => $this->getProperties(),
        ]];
    }

    private function getCollectionOperations(string $endpointPluralName): array
    {
        return [
            'get' => [
                'method' => 'GET',
                'path' => '/'.$endpointPluralName,
                'swagger_context' => [
                    'summary' => 'Retrieve a collection of images.',
                    'responses' => [
                        '200' => [
                            'description' => 'Valid response',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'total' => [
                                        'type' => 'integer',
                                        'example' => 126,
                                    ],
                                    'links' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'links' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'next' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'items' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'binary_hash' => [
                                                    'type' => 'string',
                                                ],
                                                'created' => [
                                                    'type' => 'string',
                                                    'format' => 'date-time',
                                                ],
                                                'dynamic_metadata' => [
                                                    'type' => 'object',
                                                ],
                                                'format' => [
                                                    'type' => 'string',
                                                    'example' => 'jpg',
                                                ],
                                                'hash' => [
                                                    'type' => 'string',
                                                ],
                                                'short_hash' => [
                                                    'type' => 'string',
                                                ],
                                                'height' => [
                                                    'type' => 'integer',
                                                    'example' => 42,
                                                ],
                                                'link' => [
                                                    'type' => 'string',
                                                ],
                                                'name' => [
                                                    'type' => 'string',
                                                ],
                                                'organization' => [
                                                    'type' => 'string',
                                                ],
                                                'size' => [
                                                    'type' => 'integer',
                                                    'example' => 84,
                                                ],
                                                'width' => [
                                                    'type' => 'integer',
                                                    'example' => 42,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'tags' => ['Image'],
                    'parameters' => [
                        [
                            'name' => 'hash',
                            'in' => 'query',
                            'description' => 'Filtering for the image hash',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'binaryhash',
                            'in' => 'query',
                            'description' => 'Filtering for the image binary hash',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'size',
                            'in' => 'query',
                            'description' => 'Filtering for the the image filesize in bytes',
                            'type' => 'integer',
                        ],
                        [
                            'name' => 'format',
                            'in' => 'query',
                            'description' => 'Filtering for the image format, as the file extension (e.g. "jpg")',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'width',
                            'in' => 'query',
                            'description' => 'Filtering for the image width in pixels',
                            'type' => 'integer',
                        ],
                        [
                            'name' => 'height',
                            'in' => 'query',
                            'description' => 'Filtering for the image height in pixels',
                            'type' => 'integer',
                        ],
                        [
                            'name' => 'created',
                            'in' => 'query',
                            'description' => 'Filtering for the image creation date',
                            'type' => 'string',
                            'format' => 'date-time',
                        ],
                    ],
                ],
            ],
            'post' => [
                'method' => 'POST',
                'path' => '/'.$endpointPluralName,
                'defaults' => [
                    '__api_receive' => false,
                ],
                'swagger_context' => [
                    'summary' => 'Create a new image resource.',
                    'responses' => [
                        '204' => [
                            'description' => 'New image resource has been created',
                            'schema' => $this->getGetResponseSchema(),
                        ],
                    ],
                    'tags' => ['Image'],
                    'parameters' => [
                        [
                            'name' => 'Body',
                            'in' => 'body',
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'content' => [
                                                'type' => 'string',
                                                'format' => 'byte',
                                            ],
                                            'name' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                    'meta_user' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'key' => [
                                                    'type' => 'string',
                                                ],
                                                'value' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'meta_dynamic' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'key' => [
                                                    'type' => 'string',
                                                ],
                                                'value' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getItemOperations(string $endpointPluralName): array
    {
        return [
            'get' => [
                'method' => 'GET',
                'path' => '/'.$endpointPluralName.'/{id}',
                'swagger_context' => [
                    'summary' => 'Retrieve an image by its hash.',
                    'responses' => [
                        '200' => [
                            'description' => 'Valid response',
                            'schema' => $this->getGetResponseSchema(),
                        ],
                    ],
                    'tags' => ['Image'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'description' => 'The image hash',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'delete' => [
                'method' => 'DELETE',
                'path' => '/'.$endpointPluralName.'/{id}',
                'swagger_context' => [
                    'summary' => 'Delete an image by its hash.',
                    'responses' => [
                        '200' => [
                            'description' => 'Valid response',
                        ],
                    ],
                    'tags' => ['Image'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'description' => 'The image hash',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getAttributes(): array
    {
        return [
            'pagination_enabled' => false,
        ];
    }

    private function getProperties(): array
    {
        return [
            'hash' => [
                'identifier' => true,
                'description' => 'Image hash',
                'readable' => true,
                'writable' => true,
                'readableLink' => true,
                'writableLink' => true,
                'required' => true,
                'identifier' => true,
                'iri' => 'get',
                'attributes' => null,
                'subresource' => null,
            ],
        ];
    }

    private function getGetResponseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'hash' => [
                    'type' => 'string',
                ],
                'short_hash' => [
                    'type' => 'string',
                ],
                'binary_hash' => [
                    'type' => 'string',
                ],
                'created' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'mimetype' => [
                    'type' => 'string',
                ],
                'format' => [
                    'type' => 'string',
                ],
                'size' => [
                    'type' => 'integer',
                ],
                'width' => [
                    'type' => 'integer',
                ],
                'height' => [
                    'type' => 'integer',
                ],
                'organization' => [
                    'type' => 'string',
                ],
                'link' => [
                    'type' => 'string',
                ],
            ],
        ];
    }
}
