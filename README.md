![Simple Map](resources/banner.jpg)

# Simple Map
A beautifully simple Google Map field type for **Craft 3**. Full localization support, compatible with Matrix, supports 
searching by location and sorting by distance.

[Click here for the **Craft 2.5** version.](https://github.com/ethercreative/simplemap/tree/v2)

![How it looks](resources/preview.png)

## Usage
Create the field as you would any other.  
The field type will return an array containing the following:

 - `lat` - The selected locations latitude
 - `lng` - The selected locations longitude
 - `zoom` - The zoom level of the map
 - `address` - The address of the selected location
 - `parts` - See below

This means you can use `{{ myMapField.lat }}`.

**`parts`**

This contains the locations address, broken down into its constituent parts. All values are optional so you'll need to have checks on any you use to make sure they exist.  
A list of the available values can be found [here](https://developers.google.com/maps/documentation/geocoding/intro#Types).  
To access the short version of any part, append `_short` to the end of its name.  
e.g. `{{ myMapField.country_short }}`.

### Searching and Sorting

You can search for elements using the location specified in your map field. When searching by your map field you also have the option to sort the results by distance.

```twig
{% set entries = craft.entries.myMapField({
    location: 'Maidstone, Kent, UK',
    radius: 100,
    unit: 'mi'
}).orderBy('distance').all() %}
```

- `location`: Can either be an address string (requires a Google Maps Geocoding API key) or a Lat Lng Array (`{ 'lat': 51.27219908, 'lng': 0.51545620 }`).
- `radius`: The radius around the location to search. Defaults to `50`.
- `unit`: The unit of measurement for the search. Can be either `km` (kilometers) or `mi` (miles). Defaults to `km`.

### API Keys

You can access your browser API key in templates using `craft.simpleMap.apiKey`.

### Displaying a Map

This plugin does **not** generate a front-end map; how you do that and what map library you use is up to you. However, since we have received a lot of questions asking how to do so, here are a couple of examples.
  
Using [Google Maps](https://developers.google.com/maps/documentation/javascript/tutorial):

```twig
<div id="map"></div>
<script>
var map;
function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: {
      lat: {{ entry.myMapField.lat }},
      lng: {{ entry.myMapField.lng }}
    },
    zoom: {{ entry.myMapField.zoom }}
  });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ craft.simpleMap.apiKey }}&callback=initMap" async defer></script>
```

And [Mapbox](https://www.mapbox.com/mapbox-gl-js/api/):

```twig
<script src="https://api.mapbox.com/mapbox-gl-js/v0.21.0/mapbox-gl.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v0.21.0/mapbox-gl.css" rel="stylesheet" />

<div id="map" style="width: 400px; height: 300px;"></div>
<script>
mapboxgl.accessToken = "YOUR_API_KEY";
var map = new mapboxgl.Map({
  container: "map",
  style: "mapbox://styles/mapbox/streets-v9",
  center: [
    {{ entry.myMapField.lng }},
    {{ entry.myMapField.lat }}
  ]
});
</script>
```