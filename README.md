# rokka.io - ApiPlatform Bridge

**THIS IS ALPHA SOFTWARE WITHOUT ANY TESTS, UNSTABLE AND A POC, DO NOT USE IN PRODUCTION! YOU HAVE BEEN WARNED!**

Seamlessly integrates your [rokka.io][1] account with [ApiPlatform][2].

## Why?

Why would you bridge an API that is already an API?
These are the main reasons:

* Hide the API key of rokka.io behind your application
* Use the same authentication mechanism that already protects your API to also protect rokka.io handling
* Bring a consistent feeling to your API users
* Automatically updates the Swagger docs based on the docs of rokka.io so it's always up to date

It works by simply bridging all the requests you make to the API to rokka.io by using the same path and enhancing it
with the authorization header for rokka.io. By default it does that using the `/rokka` bridge endpoint but this can be
configured.

Example: Let's say you want to create a new source image. In rokka.io that would be a `POST` request to
`/sourceimages/{organization}`.
So instead of sending a `POST` request to `https://api.rokka.io/sourceimages/{organization}` you would instead send
a `POST` request to `https://myapi.com/rokka/sourceimages/{organization}`.

Because you never want to expose the whole API for rokka.io (otherwise one could also modify your account),
the allowed endpoints have to be configured (see Configuration step).

Also, you can omit the whole `{organization}` part by configuring a `default_organization`, it will automatically
use this one whenever you request anything.

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
    
4. Add the route loader of this bundle to your routing configuration:


```yaml
# config/routes.yaml (or if you're still on SF 3: app/routing.yml)
rokka_api_platform_bridge:
    resource: .
    type: rokka_api_platform_bridge
```

5. Configure this bundle as shown in the Configuration step.

## Configuration

```yaml
rokka_api_platform_bridge:
    api_key: '' # Required
    bridge_endpoint: '/images' # Default: '/rokka'
    default_organization: ~ # Default: null
    endpoints:
        - { path: '/sourceimages/{organization}', methods: ['POST'] }
        - // etc.
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
