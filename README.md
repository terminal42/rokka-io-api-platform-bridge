# rokka.io - ApiPlatform Bridge

**THIS IS ALPHA SOFTWARE WITHOUT ANY TESTS, UNSTABLE AND A POC, DO NOT USE IN PRODUCTION! YOU HAVE BEEN WARNED!**

Seamlessly integrates your [rokka.io][1] account with [ApiPlatform][2].

## Why?

Why would you bridge an API that is already an API?
These are the main reasons:

* Hide the API key of rokka.io behind your application
* Use the same authentication mechanism that already protects your API to also protect rokka.io handling
* Bring a consistent feeling to your API users

It comes - as of today - with the following features:

* Bridges the `/sourceimages` endpoint to a configurable path in your ApiPlatform instance. This allows you to upload,
search and fetch image data as if you'd work with rokka.io directly. It also hides `organization` and normalizes the `link`
attributes so that they match the configured path.
* Full Swagger integration, automatically documenting the possibilities of the `/sourceimages` endpoint.

## Installation

1. Use [Composer][3] and run
    
    ```
    $ composer require terminal42/rokka-io-api-platform-bridge
    ```

2. Configure the [rokka.io Symfony Bundle as documented in their docs][4].
3. Load the bundles in your kernel which is done automatically if you use Symfony Flex, otherwise use

    ```php
    $bundles = [
        ...
        new Rokka\RokkaClientBundle\RokkaClientBundle(),
        new Terminal42\RokkaApiPlatformBridge\RokkaApiPlatformBridgeBundle(),
    ];
    ```
4. Optionally configure this bundle as shown in the Configuration step.

## Configuration

```yaml
terminal42_rokka_apiplatform_bridge:
    sourceimage_endpoint: '/my_endpoint_for_rokka_images' # Default: '/images'
```

## Roadmap / Ideas

* See how it goes :-D
* Unit tests once the concept works out
* Thumbnail endpoint to hide the rokka.io URL?
* Your ideas?

[1]: https://rokka.io/
[2]: https://api-platform.com/
[3]: https://getcomposer.org/
[4]: https://github.com/rokka-io/rokka-client-bundle#configuration
