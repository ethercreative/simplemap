---
title: Search by Location
---

# Search by Location

Being able to search your maps by a location is one of the most important parts 
of using a map plugin. So here's how you do it.

When building your [element query](https://docs.craftcms.com/v3/dev/element-queries/)
(in either Twig or PHP) you can pass an object to the map field that will let 
you search by your location. This object can have the following properties:

- **`location`**: An address string, map field, or `{ lat: 0, lng: 0 }` object to search by.
- **`country`**: An optional country to restrict the address string to.
- **`radius`**: The radius around the location to get results from. _Defaults to `50`_.
- **`unit`**: The distance unit to use. Can be either `mi` (miles) or `km` (kilometres). _Defaults to `km`_.

### By Address

Let's say you want to search for a location within 10 miles of a given address 
(this can be a full address, or just part of an address like a town or city 
name). In that case you would do the following:

```twig
{% set entries = craft.entries.myMapField({
    location: 'Maidstone, Kent',
    radius: 10,
    unit: 'mi',
}).all() %}
```

Here we're saying that we want to find all locations with 10 (radius) miles 
(unit) of Maidstone, Kent (location).

### By Coordinates

Alternatively you could have a set of coordinates that you want to search by. In 
that case you would pass the coordinates to the `location` parameter instead of 
an address string.

```twig
{% set entries = craft.entries.myMapField({
    location: { lat: 51.272154, lng: 0.514951 },
}).all() %}
```

By excluding the other fields we're letting them fall back to their defaults (as
specified above). In this case we're searching for all locations around those 
given coordinates within 50 kilometres.
