---
title: Get User Location
---

# Get User Location

Getting the current users location based of their IP address is easy using the 
handy Craft Twig method:

```twig
{% set userLocation = craft.maps.getUserLocation([ip]) %}
```

- **`ip`** is an optional parameter that expects a valid and not private or 
reserved IPv4 or IPv6. If null the function will use the IP address from the 
user of the current request.

This function returns a [`UserLocation`](#User-Location) which is very similar to the location
returned by a Map field, with a few added bonuses.

## User Location

The user location has the following properties:

- **`ip`** The IP address of the user (that was used to lookup the location).
- **`lat`** The latitude of the users location.
- **`lng`** The longitude of the users location.
- **`address`** The full address (see [Address](../getting-started/usage.md#address)).
- **`parts`** The separate parts of the address (see [Parts](../getting-started/usage.md#parts)).
- **`countryCode`** The ISO country code of the user locations country.
- **`isEU`** Will be true if the user is in an EU country.

### Distance

You can get the distance between the user and a given location using this method
on the User Location:

```twig
{{ userLocation.distance({ lat: 51.272154, lng: 0.514951 }, 'miles') }}
```

The method accepts two parameters:

- **`to`** A lat/lng keyed array, address string, or a Map or User location.
- **`unit`** An optional parameter specifying which unit to use for the 
measurement. Either `mi` (miles) or `km` (kilometers). _Defaults to `km`._

It will return a float of the distance between the two locations in the unit 
specified (or `km` if no unit is specified).
