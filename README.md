![Simple Map](resources/banner.jpg)

# Simple Map
A beautifully simple Google Map field type for Craft CMS. Full localization support, compatible with Matrix, supports 
searching by location and sorting by distance.

## Installation
Clone this repo into `craft/plugins/simplemap`.

## Usage
Create the field as you would any other.  
The field type will return an array containing `lat`, `lng`, `zoom`, `address`, and `parts`. This means you can use `{{ myMapField.lat }}`.

**`parts`**

This contains the locations address, broken down into its constituent parts. All values are optional so you'll need to have checks on any you use to make sure they exist.  
A list of the available values can be found [here](https://developers.google.com/maps/documentation/geocoding/intro#Types).  
To access the short version of any part, append `_short` to the end of its name. E.g. `{{ myMapField.country_short }}`.

**Searching and Sorting**

You can search for elements using the location specified in your map field. When searching by your map field you also have the option to sort the results by distance.

```twig
{% set entries = craft.entries.myMapField({
    location: 'Maidstone, Kent, UK',
    radius: 100,
    unit: 'mi'
}).order('distance') %}
```

- `location`: Can either be an address string (requires a Google Maps Geocoding API key) or a Lat Lng Array (`{ 'lat': 51.27219908, 'lng': 0.51545620 }` or `craft.simpleMap.latLng(51.27219908, 0.51545620)`).
- `radius`: The radius around the location to search. Defaults to `50`.
- `unit`: The unit of measurement for the search. Can be either `km` (kilometers) or `mi` (miles). Defaults to `km`.

**API Keys**

You can access your browser API key in templates using `craft.simpleMap.apiKey`.

![How it looks](resources/preview.png)

## Changelog

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
  
Copyright © 2015 Ether Creative <hello@ethercreative.co.uk>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.