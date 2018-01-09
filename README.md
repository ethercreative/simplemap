![Simple Map](resources/banner.jpg)

# Simple Map
A beautifully simple Google Map field type for Craft CMS. Full localization support, compatible with Matrix, supports 
searching by location and sorting by distance.

[Click here for the **Craft 3** version.](https://github.com/ethercreative/simplemap/tree/v3)

![How it looks](resources/preview.png)

## Installation
Copy the `simplemap` folder into `craft/plugins`.

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
    location: 'Maidstone, Kent',
    country: 'GB',
    radius: 100,
    unit: 'mi'
}).orderBy('distance').all() %}
```

- `location`: Can either be an address string (requires a Google Maps Geocoding API key) or a Lat Lng Array (`{ 'lat': 51.27219908, 'lng': 0.51545620 }`).
- `country`: *Optional*. Restrict the search to a specific country (useful for non-specific searches, i.e. town name). Must be valid [2-letter ISO code](https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes) (recommended), or full country name.
- `radius`: *Optional*. The radius around the location to search. Defaults to `50`.
- `unit`: *Optional*. The unit of measurement for the search. Can be either `km` (kilometers) or `mi` (miles). Defaults to `km`.

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

### Converting an address to Lat/Lng
If you need to convert a string address to a Lat/Lng you can do so using the 
`craft.simpleMap.getLatLngFromAddress($addressString[, $country])` variable.
An example of this would be wanting to convert a customers delivery address to a 
Lat/Lng, to display it on a map.

- `$address` - The string address you want to convert.
- `$country` - *Optional.* Restrict the conversion to a specific country (useful for non-specific searches, i.e. town name). Must be valid [2-letter ISO code](https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes) (recommended), or full country name. 

```twig
{% set location = craft.simpleMap.getLatLngFromAddress("Ether Creative, Maidstone", "GB") %}

{{ location.lat }}
{{ location.lng }}
```

## Changelog

### 1.8.2
- Fixed bug where distance was always `null`

### 1.8.1
- Fixed bug where maps didn't save

### 1.8.0
- The maps `parts` now contains all available options from [here](https://developers.google.com/maps/documentation/geocoding/intro#Types) (including the `_small` variants). Any options without values are returned as empty strings.
- Added `craft.simpleMap.getLatLngFromAddress($addressString[, $country])`.

### 1.7.2
- Added ability to restrict location search by country
- New icon!

### 1.7.1
- It is now possible to save the field using only an address (useful for saving via the front-end, requires Geocoding).
- Improved the field's validation.

### 1.7.0
- Improved the efficiency of the location search query.

### 1.6.3
- The field is now previewable in the entries table (via @joshuabaker)
- Fixed a bug where `SimpleMap_MapModel` `__toString()` would't always return a string.

### 1.6.2
- Fixed bug where some browsers would consider some decimals invalid in the Lat/Lng inputs #46
- Fixed `CDbException` when trying to search by invalid location. `mapField.distance` will return `NULL` in this case #48

### 1.6.1
- Added fix for slightly major bug in 1.6.0 that meant any non-translatable fields would stop displaying old data. #44

### 1.6.0
- Added alternative Server API key setting #38
- Fix maps not saving because of troubles with dots and commas #41 - @Zae
- Non-translatable fields now actually don’t translate #43
- Added error handling for failed server geocode requests #37
- You can no longer tap to move the marker on mobile (drag map instead) #33

### 1.5.1
- Fixed the Lat/Lng inputs not accepting decimals in Chrome #36

### 1.5.0 - *The "about time" update*
- Added [FeedMe 2](https://sgroup.com.au/plugins/feedme) support! (thanks to @engram-design, #23)
- Added support for [Neo](https://github.com/benjamminf/craft-neo). #21
- Fixed when using the address search, locations that are non-specific (i.e. the name of a country) will no longer be forced to be specific.
- Fixed "Unnamed Roads" no longer change to a different unnamed road when the map is re-loaded. #26
- Disabled scrollwheel on map, #27
- Details about the selected location are now populated server-side, no more JavaScript shittery! #30
- Improved locale specific address details #24
- If the query limit is reached, the map will re-try after 1 second, a maximum of 5 times. #19
- The address of the map will be returned when output as a string, i.e. `{{ entry.myMapField }}`. #29

### 1.4.0
- Changed Git folder structure to exclude surplus files from plugin directory
- Lat / Lng fields are now visible and editable
- Added option to hide Lat / Lng fields

### 1.3.0
- Added option to hide the map, leaving only the address input
- Added ability to restrict auto-complete by country, address type, and boundary
- Added `_short` prefix to all parts, returning the short value. **You will need to re-save your entries for this to take effect.**
- Fixed map JS erroring when in globals
- Merged API keys into one

### 1.2.4
- The address input automatically updates the map after paste

### 1.2.3
- Browser API key can now be accessed in templates

### 1.2.2
- Added Browser API key setting

### 1.2.1
- Fixed bug where map would not display correctly when in a secondary tab.

### 1.2.0
- **Added search and sorting support.**
- Added optional Google API Server Key setting
- **_WARNING:_** This update will break any map fields that are NOT standalone (global) or in a Matrix field.

### 1.1.2
- Fix \#5 via @jripmeester - Fixed Lat / Lng being populated with function, not number.

### 1.1.1
- Added `parts` to the fieldtype output.

### 1.1.0
- Merged \#2 & \#3 from @cballenar
- \#2 - Field now stores map zoom
- \#3 - Improved handling of unknown locations
- Improved error message display

### 1.0.2
- Added link to docs
- Added releases json for updates

### 1.0.1
- Fixed: Hidden Lat/Lng/Address fields are now cleared when the Address Search input is empty.

### 1.0.0
- Initial Release

---
  
Copyright © 2017 Ether Creative <hello@ethercreative.co.uk>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.