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