## 3.4.11 - 2019-04-05
### Fixed
- Map records are no longer double saved when upgrading to from Craft 2 to 3

## 3.4.10 - 2019-04-04
### Fixed
- Map records are no longer double saved when upgrading to >3.4.x

## 3.4.9 - 2019-04-01
### Added
- Added option to show lat / lng fields

### Fixed
- Fixed map not validating correctly
- Fixed wrong map value being shown on element index with multiple sites
- Fixed missing table prefix in map element query
- Fixed migration issue when upgrading due to duplicate element IDs

### Improved
- Scrolling to zoom disabled on map
- Clearing the map will no longer store the default data

## 3.4.8 - 2019-03-27
### Fixed
- Fix error when migrating a field from Craft 2 when `countryRestriction` isn't set
- Location search excludes elements that have been soft-deleted
- Fixed issue restoring trashed elements that have a map field
- Map field elements are trashed and deleted correctly
- Fixed syntax issue on PHP <7.1.0
- Fixed error during repair migration when element doesn't exist

## 3.4.7 - 2019-03-25
### Fixed
- Fixed JS error when clearing field
- Fixed missing parts when using Google maps for geo-coding

### Improved
- Clear button now translatable

## 3.4.6 - 2019-03-25
### Added
- Added "Clear" button
- Always show full address field even if address block is hidden

### Fixed
- The really shitty element stuff. Is good now. I think.

## 3.4.5 - 2019-03-25
### Fixed
- Fixed maps failing to get value after save

### Changed
- Using Google Maps geo service will result in legacy parts always being used, 
meaning you can access all available address components.

## 3.4.4 - 2019-03-22
### Fixed
- Fixed some issues when upgrading from older versions of Maps. We recommend 
upgrading from 3.3.4 or lower directly to this release or later.

## 3.4.3 - 2019-03-20
### Changed
- You can now pass a map to the location query (fixes #99)

### Fixed
- Fixed issue when `cp-field-inspect` plugin is installed (fixes #127)
- Fixed `elementId cannot be null` error on saving new entries with map fields (fixes #126)

## 3.4.2 - 2019-03-20
### Fixed
- Fixed issue setting old field settings after upgrade.

## 3.4.1 - 2019-03-20
### Fixed
- Fixed an issue where the map field class broke after upgrading.

## 3.4.0 - 2019-03-20

> {warning} This is a major update, we strongly recommend taking a database backup before updating!

### Changed
- SimpleMap is now Maps! We've re-written the plugin from the ground-up while 
keeping it backwards compatible (even back to Craft 2!)
- Maps is now powered by Vue!
- New icon yo

### Added 
- OpenStreetMap Support and map tiles
- Mapbox Support and map tiles
- Apple MapKit Map Tiles
- Here Maps Support and map tiles
- Wikimedia Map Tiles
- Carto Map Tiles
- Address inputs for manually settings address parts data.

### Improved
- We've normalized the map "Parts", so you'll always know what data you have available.
- CraftQL support: you can now query and mutate Maps fields via Graph!
- Field Customization: It's now possible to hide the location search, map, and address inputs.

### Fixed
- Maps are now multi-site aware and can be translated.

### Removed
- Removed lat/lng inputs from field
- Removed restrict by type
- Removed boundary restriction

## 3.3.4 - 2018-09-05
### Fixed
- Fixed a bug where SimpleMap would not validate required fields. (via @samhibberd)

## 3.3.3 - 2018-03-13
### Fixed
- Fixed a bug where SimpleMap would cause the `ResaveElements` job to error when triggered via console.

## 3.3.2 - 2018-03-05
### Added
- Added docs for using a config file to configure the plugin.

### Fixed
- Fixed JOIN alias issue when using the Element API plugin (via @idontmessabout)

## 3.3.1 - 2018-01-30
### Fixed
- Fixed JS bug on settings page

## 3.3.0 - 2018-01-30
### Fixed
- Added a fix for those annoying `Call to a member function getMap() on null` bugs

### Improved
- Map height no longer jumps when page loads
- Vastly improved the map fields settings UI/UX
	- No more nasty text fields!
	- Map height and position is now set by resizing and moving a map
	- Auto-complete search bounds can now be drawn directly onto a map
	- Radio buttons are now drop-downs

### Changed
- Now using the plugins `afterInstall` function instead of the plugin after install event
- The "Hide Lat/Lng" option is now true by default

## 3.2.0 - 2018-01-25
### Fixed
- Fixed bug where pagination would error when querying via a map field. #70

### Improved
- Updated CraftQL support (via @markhuot)
- Removed webonyx/graphql-php dependency #71
- Improved address and lat/lng input sizing on smaller screens and in a HUD #73
- Updated Mapbox example to use latest API #74

## 3.1.3 - 2017-12-18
### Fixed
- Map fields no longer cause global sets to fail to save!

## 3.1.2 - 2017-12-18
### Fixed
- Fixed settings not translating for non-English languages
- Fixed boundary settings fields not accepting decimals

## 3.1.1 - 2017-11-30
### Fixed
- Fixed bug where maps were failing to save.

## 3.1.0 - 2017-11-30
### Added
- [CraftQL](https://github.com/markhuot/craftql) support!
- Added `craft.simpleMap.getLatLngFromAddress($addressString[, $country])`.

### Improved
- The maps `parts` now contains all available options from [here](https://developers.google.com/maps/documentation/geocoding/intro#Types) (including the `_small` variants). Any options without values are returned as empty strings.

## 3.0.4 - 2017-11-28
### Added
- Added ability to restrict location search by country

### Changed
- New icon!

## 3.0.3 - 2017-11-08
### Added
- It's now possible to save the map field with only an address! Useful for populating the field from the front-end. (Requires the Geocoding API).

### Improved
- The address and lat/lng are now validated.

## 3.0.2 - 2017-11-03
### Fixed
- Fixed a bug where location searches would error if `orderBy` was not defined

## 3.0.1 - 2017-11-03
### Fixed
- Fixed maps not rendering

## 3.0.0 - 2017-11-03
### Changed
- Initial Craft 3 Release
