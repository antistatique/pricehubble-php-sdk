Pricehubble PHP SDK
=============

Super-simple, minimum abstraction Pricehubble API v1.x wrapper, in PHP.

I hate complex wrappers. This lets you get from the Pricehubble API docs to the code as directly as possible.

Requires PHP 7.4+. Abstraction is for chimps.

[![Build](https://github.com/antistatique/pricehubble-php-sdk/actions/workflows/cs-tests.yml/badge.svg)](https://github.com/antistatique/pricehubble-php-sdk/actions/workflows/cs-tests.yml)
[![Coverage Status](https://coveralls.io/repos/github/antistatique/pricehubble-php-sdk/badge.svg)](https://coveralls.io/github/antistatique/pricehubble-php-sdk)
[![Packagist](https://img.shields.io/packagist/dt/antistatique/pricehubble-php-sdk.svg?maxAge=2592000)](https://packagist.org/packages/antistatique/pricehubble-php-sdk)
[![License](https://poser.pugx.org/antistatique/pricehubble-php-sdk/license)](https://packagist.org/packages/antistatique/pricehubble-php-sdk)

Getting started
------------

You can install `pricehubble-php-sdk` using Composer:

```
composer require antistatique/pricehubble-php-sdk
```

Examples
--------

### Basic Example

Start by `use`-ing the class and creating an instance with your API key

```php
use \Antistatique\Pricehubble\Pricehubble;
```

Every request should contain a valid access token. use the `Pricehubble::authenticate` method prior any requests.

### Valuation

Performs valuations for the specified real estate properties on the specified valuation dates. The endpoint can be used to do a valuation of a single property, to create time series or to perform bulk valuations.

The number of valuations per call may not exceed 50, i.e. you can perform valuations for 1 property on 50 dates or for 50 properties on 1 date, but not for 50 properties on 50 dates.

👉 https://docs.pricehubble.com/international/valuation/

```php
$pricehubble = new Pricehubble();
$pricehubble->authenticate($username, $password)
$response = $pricehubble->valuation()->full([
    'dealType' => 'sale',
    'valuationInputs' => [
        [
            'property' => [
                'location' => [
                    'address' => [
                        'postCode' => 8037,
                        'city' => 'Zürich',
                        'street' => 'Nordstrasse',
                        'houseNumber' => '391'
                    ],
                ],
                'buildingYear' => 1850,
                'livingArea' => 1500.00,
                'propertyType' => [
                    'code' => 'apartment'
                ],
            ],
        ],
    ],
    'countryCode' => 'CH',
]);
print_r($response);
```

### Valuation Light

Performs a simple valuation of the specified property.

If you would like to perform valuations for multiple properties (in a single call), create time series, or achieve better valuations by taking more parameters into account, consider using the full-fledged Valuation endpoint.

👉 https://docs.pricehubble.com/international/valuation_light/

```php
$pricehubble = new Pricehubble();
$pricehubble->authenticate($username, $password)
$response = $pricehubble->valuation()->full([
    'dealType' => 'sale',
    'property' => [
        'location' => [
            'address' => [
                'postCode' => 8037,
                'city' => 'Zürich',
                'street' => 'Nordstrasse',
                'houseNumber' => '391'
            ],
        ],
        'buildingYear' => 1850,
        'livingArea' => 1500.00,
        'propertyType' => [
            'code' => 'apartment'
        ],
    ],
    'countryCode' => 'CH',
]);
print_r($response);
```

### Points of Interest

Returns points of interests such as schools, shops, etc. that match the specified search criteria.

👉 https://docs.pricehubble.com/international/pois/

```php
$pricehubble = new Pricehubble();
$pricehubble->authenticate($username, $password)
$response = $pricehubble->pointsOfInterest()->gather([
    'coordinates' => [
        'latitude' => 47.3968601,
        'longitude' => 8.5153549,
    ],
    'radius' => 1000,
    'category' => [
        'education',
        'leisure',
    ],
    'subcategory' => [
        'kindergarten',
        'playground',
    ],
    'offset' => 0,
    'limit' => 10,
    'countryCode' => 'CH',
]);
print_r($response);
```

Troubleshooting
---------------

To get the last error returned by either the HTTP client or by the API, use `getLastError()`:

```php
echo $pricehubble->getLastError();
```

For further debugging, you can inspect the headers and body of the response:

```php
print_r($pricehubble->getLastResponse());
```

If you suspect you're sending data in the wrong format, you can look at what was sent to Pricehubble by the wrapper:

```php
print_r($pricehubble->getLastRequest());
```

If your server's CA root certificates are not up to date you may find that SSL verification fails and you don't get a response. The correction solution for this [is not to disable SSL verification](http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/). The solution is to update your certificates. If you can't do that, there's an option at the top of the class file. Please don't just switch it off without at least attempting to update your certs -- that's lazy and dangerous. You're not a lazy, dangerous developer are you?
