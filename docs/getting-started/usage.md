---
title: Usage
---

# Usage

## Creating a Map field

You can create a Map field in the same way you would create any other field in 
Craft. Simple go to "Settings" -> "Fields" and click the "New Field" button.
Fill out the fields required and select "Map" from the "Field Type" select.

You can now configure the initial state of the map and how it appears to the 
user. Use the Map to select the initial location of the map and the fields
below the map to customize the layout and any restrictions.

## Displaying the Map

When accessing the Map field in twig you will have access to the following 
properties:

- **`lat`** The latitude of the selected maps location
- **`lng`** The longitude of the selected maps location
- **`zoom`** The zoom level of the map
- **`address`** The full address (see [Address](#address))
- **`parts`** The separate parts of the address (see [Parts](#parts))
- **`distance`** The distance the location is from your search (only populated when [Searching](#searching))

### Address
The `address` comes in two flavours. The first is as a string, and will output
the Full Address as it appears in the Map field.

```twig
{{ myMapField.address }}
```

The second is as a function, `address([exclude = [] [, glue = '<br/>']])`, which 
will allow you to output the address in a more formatted way. Both arguments are 
optional.

- **`exclude`** expects an array of [Parts](#parts) that you don't want to 
appear when outputting the address. _Defaults to `[]`_.
- **`glue`** is the string that joins the parts together. _Defaults to `'<br/>'`.

```twig
{{ myMapField.address(['country'], ', ') }}
```

The example above is outputting the address as a comma-separated string, 
excluding the country.

### Parts

The parts contains the, well, parts that make up the address.

- **`number`** The name or number of the location
- **`address`** The street address of the location (not the full address)
- **`city`** The city in which the location is situated
- **`postcode`** The postal or zip code of the location
- **`county`** The county of the location
- **`state`** The state or region of the location
- **`country`** The locations country


- **`planet`** The planet of the location
- **`system`** The system containing the planet of the location
- **`arm`** The galactic arm of the location
- **`galaxy`** The galaxy the arm is attached to
- **`group`** The group the locations galaxy belongs to
- **`cluster`** The galaxy cluster containing the group
- **`supercluster`** The supercluster the galaxy belongs to

```twig
{{ myMapField.parts.city }}
```

You can also access the parts directly from the map with the exception of the
`address` part, which can be accessed via `streetAddress`.

```twig
{{ myMapField.streetAddress }}
{{ myMapField.city }}
```

### Searching

When querying elements you can filter them by proximity to a given location. To
do so, simply pass an address and radius to the Map field in the 
[element query](https://docs.craftcms.com/v3/dev/element-queries/).

- **`location`** An address string, map field, or `{ lat: 0, lng: 0 }` object to 
search by.
- **`country`** An optional country to restrict the address string to.
- **`radius`** The radius around the location to get results from. _Defaults to `50`_.
- **`unit`** The distance unit to use. Can be either `mi` or `km`. _Defaults to `km`_.

```twig
{% set entries = craft.entries.myMapField({
    location: 'Maidstone, Kent',
    country: 'UK',
    radius: 100,
    unit: 'mi',
}).all() %}
```

If you search using this method you will have access to the `distance` property
in the resulting elements Map fields. This will return the distance that 
location is from the location searched for, in the unit specified when searching.

You can also sort by `distance` when searching for an address.

```twig
{% set entries = craft.entries.myMapField({
    location: { lat: 51.272154, lng: 0.514951 },
}).orderBy('distance').all() %}
```

```twig
{% set entries = craft.entries.myMapField({
    location: myOtherMapField,
}).orderBy('distance desc').all() %}
```
