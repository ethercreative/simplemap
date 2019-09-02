---
title: Static Map Images
---

# Static Map Images

**Maps** makes it really easy to render static map images in twig. There are two
ways of rendering a static map image. First is via a Map field, second is using 
the global map variable.

## Options

Both methods support the following options, passed as a Twig object to the 
method (we'll cover that later):

- **`center`** - This can be an address string (i.e. "Maidstone, UK"), a lat / 
lng variable (i.e. `[51.272154, 0.514951]` or `{ lat: 51.272154, lng: 0.514951 }`).
- **`width`** - The width of the map image (see max image sizes below).
- **`height`** - The height of the map image (see max image sizes below).
- **`zoom`** - The zoom level of the map (must be between 0 and 18).
- **`scale`** - The scale of the image (can be either 1 or 2 (retina), defaults to 1).

## From a Map field

To render a map from a Map field, use the `img` or `imgSrcSet` methods on the 
fields value:

```twig
{% set myMapField = entry.myMapField %}
<img
    src="{{ myMapField.img() }}"
    srcset="{{ myMapField.imgSrcSet() }}"
    alt="{{ myMapField.address }}"
/>
```

### `img([options])`

The `img` method returns the URL for the static map image. It accepts an 
[options](#options) object as its only parameter. Since we already
have a location and zoom level from the Map field, the `center` and `zoom` 
options will be ignored.

```twig
{{ entry.mapField.img({
    width: 800,
    height: 600,
}) }}
```

### `imgSrcSet([options])`

`imgSrcSet` is similar to `img` accept it returns a `srcset` ready string, 
supporting @1x and @2x resolutions. As with `img` it accepts an 
[options](#options) object as its only parameter. Along with the `center` and 
`zoom` options being ignored (as with `img`), the `scale` option is also 
ignored.

## Using the global `map` variable

...todo...
