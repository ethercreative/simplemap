---
title: User Location Redirecting
---

# User Location Redirecting

With Maps it is now possible to redirect the user to a specific site based off 
their physical location. You can do this by setting the 
[`geoLocationRedirectMap`](../getting-started/config.md#geolocationredirectmap)
in the [`simplemap.php`](../getting-started/config.md) config file.

The value of `geoLocationRedirectMap` should be a keyed array. The key of each
item should be the handle for the site you want to redirect to. The value of 
each item should be an array of properties to match against 
([explained below](#location-matching)), or a string containing an asterisk `*`
 which will act as a catch-all.

## Location Matching

Location matching is performed by looking at each key/value pair in an array and
checking if they all match the users location. The keys should match the 
available properties in the [User Location](./get.md#user-location). The values
should be an exact match for the contents of the User Location.

The priority is top-down, first come first serve. This means that the first site
that matches the users location will be the one that is used.

See the example below for a visual explanation.

### Example

```php
<?php

return [
    'geoLocationRedirectMap' => [
        'uk'       => [ 'countryCode' => ['uk', 'ie'] ],
        'eu'       => [ 'isEU' => true ],
        'southern' => [ 'lat' => function ($lat) { return $lat <= 0; } ],
        'global'   => '*',
    ],
];
```

The first site, `uk`, is checking to see if the `countryCode` of the users 
location matches either `'uk'` or `'ie'` because we want to bundle the Irish in 
with the English. I'm sure they won't mind. If it does match, the user will be 
redirected to the current page on the UK site.

The second site, `eu`, checks to see if the user is in the EU and will redirect
to the `eu` site if they are.

`southern` is using an anonymous function, or lambda, to check if the users 
latitude is on or below the equator. You can use lambdas for any of the 
properties. The first and only argument will be the value of that property on 
the users location. All lambdas must return a boolean value.

The final site, `global`, uses an asterisk as a catch-all. This means that if 
none of the previous rules match this one will be used. You should always use 
the wildcard last since any subsequent rules will be ignored.
