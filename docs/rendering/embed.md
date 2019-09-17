---
title: Embed Dynamic Maps
---

# Embed Dynamic Maps

With **Maps** you can quickly and easily output an interactive map using one of
two templating methods. The first is via a Map field, and the second is using 
the global map variable.

## Options

*The available options include those from the [Static Map](./static.md#options)
as well as two additional options:*

- **`id`** - The ID to use when creating the map (using in JavaScript and on the 
             HTML tag).
- **`options`** - An object of options that will be passed to the JavaScript, 
                  allowing you to customise the map according to the map library 
                  being used.

## From a Map field

Both of the below `embed` methods will return a div tag with the given (or 
generated) ID, and will include the necessary JavaScript and CSS to render the 
map. They both accept an [options](#options) object as their only parameter.

### `mapField.embed([options])`

To render a dynamic map from a Map field, use the `embed` method on the fields 
value. The `center` and `zoom` options will be ignored, since their values are 
gathered from the Map field's value.

```twig
{{ myMapField.embed({
    id: 'map',
    markers: [{}],
}) }}
```

### `craft.maps.embed([options])`

```twig
{{ craft.maps.embed({
    center: 'Maidstone, UK',
    options: {
        disableDefaultUI: true,
        draggable: false,
    },
}) }}
```

## Additional attributes

If you want to add additional attributes to the output div from the `embed` 
methods you should do so using Craft's built-in [`|attr` filter](https://docs.craftcms.com/v3/dev/filters.html#attr).

```twig
{{ myMapField.embed()|attr({
    class: 'map',
}) }}
```

## Libraries & Caveats

### Google Maps
The Google Maps service uses Google's [Maps JavaScript library](https://developers.google.com/maps/documentation/javascript/reference/).
You can view the options that you can pass to `options.options` [here](https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions).

**Caveats**
- Google Maps doesn't support coloured markers.

### Apple Maps
Apple Maps uses the [Mapkit JS library](https://developer.apple.com/documentation/mapkitjs).
You can view the options [here](https://developer.apple.com/documentation/mapkitjs/mapconstructoroptions).

### Mapbox
Mapbox uses [Mapbox](https://docs.mapbox.com/mapbox-gl-js/api/). You can view 
the options [here](https://docs.mapbox.com/mapbox-gl-js/api/#map).

**Caveats**
- Mapbox doesn't support marker labels.

### Here
Here uses [Here](https://developer.here.com/documentation/maps/topics/overview.html).
You can view the options [here](https://developer.here.com/documentation/maps/topics_api/h-map-options.html).

### Others
Wikimedia, OSM, and Carto all use [Leaflet JS](https://leafletjs.com/). You can
view the options [here](https://leafletjs.com/reference-1.5.0.html#map-option).
