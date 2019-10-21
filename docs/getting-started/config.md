---
title: Configuration
---

# Configuration

There are two ways to configure **Maps**, via the Craft CP or using a config
file (for advanced configuration).

### Services & Tokens

Below is a list of the various services supported by Maps and links on how to
get their tokens.

#### Map Tiles & Geocoding

##### OpenStreetMap / Nominatim
No token required

##### Wikimedia
No token required

##### Carto
No token required

##### [Google](https://cloud.google.com/maps-platform/#get-started)
You will need to enable the **Maps JavaScript API** and **Places API** for if
using Google for the map tiles, and the **Places API** and **Geocoding API** if
you are using it for the Geo service.

##### [Mapbox](https://docs.mapbox.com/help/how-mapbox-works/access-tokens/)
You can use the same key for both map tiles and geo service, no configuration
needed!

##### [Apple MapKit](https://developer.apple.com/documentation/mapkitjs/setting_up_mapkit_js)
We currently only support Apple MapKit for map tiles only.

##### [Here](https://developer.here.com/)
You can use the same key for both map tiles and geo service, no configuration
needed!

#### Geolocation

##### [ipstack](https://ipstack.com/product)
ipstack offer free and paid-for versions of their API.

##### MaxMind Lite
No token required

##### [MaxMind](https://www.maxmind.com/en/geoip2-precision-services)
MaxMind offer free lookup database that must be stored locally, and a more
accurate paid-for version of their API.

## CP

You can get to the Maps settings in the Craft CP by navigating to "Settings" ->
"Maps" in an environment where `allowAdminChanges` is set to `true`.

## Config File

For advanced configuration create a `simplemap.php` file in your `config` folder.
This file should return an array of Maps settings.

```php
<?php

return [
    'mapToken' => '123abc',
];
```

### Settings

#### `mapTiles`
_Default: `MapTiles::Wikimedia`_

The map tileset to use. Must be set to one of the `MapTiles` constants.

```php
<?php

use ether\simplemap\enums\MapTiles;

return [
    'mapTiles' => MapTiles::CartoVoyager,
];
```

#### `mapToken`
_Default: `''`_

The token to use with your selected map tileset. This is only required when you
are using a tileset that requires a token.

**Mapbox & Google Maps**
For these services your token should be a string containing the token.

```php
<?php

return [
    'mapToken' => '',
];
```

**Apple MapKit**
Your token should be an array containing `privateKey`, `teamId`, `keyId`.

```php
<?php

return [
    'mapToken' => [
        'privateKey' => '',
        'teamId'     => '',
        'keyId'      => '',
    ],
];
```

**Here**
The token should be an array containing `appId`, `apiKey`, `appCode`.

```php
<?php

return [
    'mapToken' => [
        'appId'   => '',
        'apiKey'  => '',
        'appCode' => '',
    ],
];
```

#### `geoService`
_Default: `GeoService::Nominatim`_

The geocoding service to use. Must be set to one of the `GeoService` constants.

```php
<?php

use ether\simplemap\enums\GeoService;

return [
    'geoService' => GeoService::GoogleMaps,
];
```

#### `geoToken`
_Default: `''`_

The token to use with your selected geocoding service. This is only required
when you are using a geocoding that requires a token.

**Mapbox & Google Maps**
For these services your token should be a string containing the token.

```php
<?php

return [
    'geoToken' => '',
];
```

**Here**
The token should be an array containing `appId`, `appCode`.

```php
<?php

return [
    'geoToken' => [
        'appId'   => '',
        'appCode' => '',
    ],
];
```

#### `geoLocationService`
_Default: `GeoLocationService::None`_

The geolocation service to use. Must be set to one of the `GeoLocationService`
constants.

```php
<?php

use ether\simplemap\services\GeoLocationService;

return [
    'geoLocationService' => GeoLocationService::MaxMindLite,
];
```

#### `geoLocationToken`
_Default: `''`_

The token to use with your selected geolocation service. This is only required
when you are using a geolocation that requires a token.

**ipstack**
For this services your token should be a string containing the token.

```php
<?php

return [
    'geoLocationToken' => '',
];
```

**MaxMind**
The token should be an array containing `accountId`, `licenseKey`.

```php
<?php

return [
    'geoLocationToken' => [
        'accountId'  => '',
        'licenseKey' => '',
    ],
];
```

#### `geoLocationCacheDuration`
_Default: `'P2M'`_

A string (a [duration interval](https://en.wikipedia.org/wiki/ISO_8601#Durations))
or int (in seconds) of how long we should cache IP lookups.

#### `geoLocationAutoRedirect`
_Default: `false`_

Will automatically redirect the user according to `geoLocationRedirectMap` when
set to true.

#### `geoLocationRedirectMap`
_Default: `[]`_

This dictates what site the user is redirected to based off their IPs location.

It should be a key value array where key is the handle of the site to redirect,
and value is a key value array of user location properties and their required
matches or an string to catch all.

For more details on how to setup your geolocation redirects have a look at the
[Geolocation / Redirect](../geolocation/redirect.md) docs.

```php
<?php

return [
    'geoLocationRedirectMap' => [
        'uk'     => [ 'country' => 'uk' ],
        'eu'     => [ 'isEU' => true ],
        'global' => '*',
    ],
];
```
