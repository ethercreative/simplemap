import GeoService from '../enums/GeoService';

export default class Parts {

	// Properties
	// =========================================================================

	number   = '';
	address  = '';
	city     = '';
	postcode = '';
	county   = '';
	state    = '';
	country  = '';

	constructor (parts, service) {
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

	/**
	 * Create from existing parts (i.e. from server)
	 *
	 * @param parts
	 * @return {Parts}
	 */
	static from (parts) {
		const p = new Parts();

		p.number   = parts.number || '';
		p.address  = parts.address || '';
		p.city     = parts.city || '';
		p.postcode = parts.postcode || '';
		p.county   = parts.county || '';
		p.state    = parts.state || '';
		p.country  = parts.country || '';

		return p;
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
		this.number = this._join([
			parts.house_number,
			parts.address29,
			[
				'pedestrian',
				'footway',
				'path',
				'road',
				'neighbourhood',
				'suburb',
				'village',
				'town',
				'city_district',
				'city',
			].indexOf(parts.type) === -1 ? parts[parts.type] : null,
		]);

		this.address = this._join([
			parts.pedestrian,
			parts.footway,
			parts.path,
			parts.road,
			parts.neighbourhood,
			parts.suburb,
		]);

		this.city = this._join([
			parts.village,
			parts.town,
			parts.city_district,
			parts.city,
		]);

		this.postcode = parts.postcode;
		this.county = parts.county;

		this.state = this._join([
			parts.state_district,
			parts.state,
		]);

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

		this.number   = parts.number;
		this.address  = parts.address;
		this.city     = parts.place;
		this.postcode = parts.postcode;
		this.county   = parts.district;
		this.state    = parts.region;
		this.country  = parts.country;
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

		this.number = this._join([
			parts.subpremise,
			parts.premise,
			parts.street_number,
		]);

		this.address = this._join([
			parts.route,
			parts.neighborhood,
			parts.sublocality_level_5,
			parts.sublocality_level_4,
			parts.sublocality_level_3,
			parts.sublocality_level_2,
			parts.sublocality_level_1,
			parts.sublocality,
		]);

		this.city = this._join([
			parts.postal_town,
			parts.locality,
		]);

		this.postcode = parts.postal_code || parts.postal_code_prefix;
		this.county = parts.administrative_area_level_2;
		this.state = parts.administrative_area_level_1;
		this.country = parts.country;
	}

	// Helpers
	// =========================================================================

	/**
	 * Filters and joins the given array
	 *
	 * @param {array} parts
	 * @return {string}
	 * @private
	 */
	_join (parts) {
		return parts.filter(Boolean).join(', ');
	}

}
