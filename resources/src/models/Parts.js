import GeoService from '../enums/GeoService';

export default class Parts {

	// Properties
	// =========================================================================

	_raw = null;

	number = '';
	address = '';
	city = '';
	postcode = '';
	county = '';
	state = '';
	country = '';

	constructor (parts, service) {
		this._raw = parts;

		switch (service) {
			case GeoService.Nominatim:
				this._nominatim(parts);
				break;
			case GeoService.Mapbox:
				this._mapbox(parts);
				break;
			case GeoService.GoogleMaps:
				this._google(parts);
				break;
			default:
				return this;
		}
	}

	// Helpers
	// =========================================================================

	/**
	 * Parse Nominatim parts
	 *
	 * @param parts
	 * @private
	 */
	_nominatim (parts) {
		this.number = parts.house_number;
		this.address = parts.road;
		this.city = parts.city || parts.town;
		this.postcode = parts.postcode;
		this.county = parts.county;
		this.state = parts.state;
		this.country = parts.country;
	}

	/**
	 * Parse Mapbox parts
	 *
	 * @param parts
	 * @private
	 */
	_mapbox (parts) {
		parts = parts.context.reduce((a, part) => {
			const key = part.id.split('.')[0];
			a[key] = part.text;

			return a;
		}, {
			number: parts.address,
			[parts.place_type[0]]: parts.text,
		});

		this.number = parts.number;
		this.address = parts.address;
		this.city = parts.place;
		this.postcode = parts.postcode;
		this.county = parts.district;
		this.state = parts.region;
		this.country = parts.country;
	}

	/**
	 * Parse Google Maps parts
	 *
	 * @param parts
	 * @private
	 */
	_google (parts) {
		parts = parts.reduce((a, part) => {
			const key = part.types[0];
			a[key] = part.long_name;

			return a;
		}, {});

		this.number = [
			parts.subpremise,
			parts.premise,
			parts.street_number,
		].filter(Boolean).join(', ');

		this.address = [
			parts.route,
			parts.neighborhood,
			parts.sublocality_level_5,
			parts.sublocality_level_4,
			parts.sublocality_level_3,
			parts.sublocality_level_2,
			parts.sublocality_level_1,
			parts.sublocality,
		].filter(Boolean).join(', ');

		this.city = [
			parts.postal_town,
			parts.locality,
		].filter(Boolean).join(', ');

		this.postcode = parts.postal_code || parts.postal_code_prefix;
		this.county = parts.administrative_area_level_2;
		this.state = parts.administrative_area_level_1;
		this.country = parts.country;
	}

}
