import GeoService from '../enums/GeoService';
import Parts from '../models/Parts';
import PartsLegacy from '../models/PartsLegacy';
import waitForGlobal from '../helpers/waitForGlobal';

export default class Geo {

	// Properties
	// =========================================================================

	country = null;
	service = null;
	token = null;
	locale = null;

	google = { service: null, session: null };
	apple = { Search: null };

	// Constructor
	// =========================================================================

	constructor ({ country, geoService: service, geoToken: token, locale }) {
		this.country = country ? country.toLowerCase() : null;
		this.service = service;
		this.token = token;
		this.locale = locale;

		if (service === GeoService.GoogleMaps) {
			waitForGlobal('google', () => this.initGoogle());
		} else if (service === GeoService.AppleMapKit) {
			waitForGlobal('mapkit', () => this.initApple(token));
		}
	}

	// Initializers
	// =========================================================================

	initGoogle () {
		this.google = {
			service: new window.google.maps.places.AutocompleteService(),
			session: new window.google.maps.places.AutocompleteSessionToken(),
			geocoder: new window.google.maps.Geocoder(),
			places: new window.google.maps.places.PlacesService(
				document.createElement('div')
			),
		};
	}

	initApple (token) {
		window.mapkit.init({
			authorizationCallback: done => done(token),
		});

		this.apple = {
			Search: new window.mapkit.Search(),
			Geocoder: new window.mapkit.Geocoder(),
			Coordinate: window.mapkit.Coordinate,
		};
	}

	// Actions
	// =========================================================================

	// Actions: Search
	// -------------------------------------------------------------------------

	/**
	 * Run the search
	 *
	 * @param {string} text
	 * @returns {Promise<Array>}
	 */
	async search (text) {
		if (!text || text.trim() === '') {
			return [];
		}

		let suggestions = [];

		switch (this.service) {
			case GeoService.Nominatim:
				suggestions = await this.searchNominatim(text);
				break;
			case GeoService.Mapbox:
				suggestions = await this.searchMapbox(text);
				break;
			case GeoService.GoogleMaps:
				suggestions = await this.searchGoogle(text);
				break;
			case GeoService.AppleMapKit:
				suggestions = await this.searchApple(text);
				break;
			case GeoService.Here:
				suggestions = await this.searchHere(text);
				break;
			default:
				throw new Error('Unknown geocoding service: ' + this.service);
		}

		return suggestions;
	}

	/**
	 * Search using Nominatim
	 *
	 * @param {string} query
	 * @returns {Promise<*>}
	 */
	async searchNominatim (query) {
		const params = new URLSearchParams({
			q: query,
			format: 'jsonv2',
			limit: 5,
			addressdetails: 1,
			countrycodes: this.country,
			'accept-language': this.locale,
		}).toString();

		const data = await fetch(
			'https://nominatim.openstreetmap.org/search?' + params
		).then(res => res.json());

		return data.map(result => ({
			address: result.display_name,
			lat: result.lat,
			lng: result.lon,
			parts: new Parts({
				...result.address,
				type: result.type,
			}, GeoService.Nominatim),
		}));
	}

	/**
	 * Search using Mapbox
	 *
	 * @param {string} query
	 * @returns {Promise<*>}
	 */
	async searchMapbox (query) {
		const rawParams = {
			types: 'address,country,postcode,place,locality,district,neighborhood',
			limit: 5,
			access_token: this.token,
			language: this.locale,
		};

		if (this.country)
			rawParams.country = this.country;

		const params = new URLSearchParams(rawParams).toString();

		const data = await fetch(
			'https://api.mapbox.com/geocoding/v5/mapbox.places/' + query + '.json?' + params
		).then(res => res.json());

		return data.features.map(result => ({
			address: result.place_name,
			lat: result.center[1],
			lng: result.center[0],
			parts: new Parts(result, GeoService.Mapbox),
		}));
	}

	/**
	 * Search using Google Places
	 *
	 * @param {string} query
	 * @returns {Promise<*>}
	 */
	searchGoogle (query) {
		return new Promise(resolve => {
			this.google.service.getPlacePredictions({
				input: query,
				sessionToken: this.google.session,
				componentRestrictions: {
					country: this.country,
				},
			}, predictions => {
				if (!predictions)
					return resolve([]);

				return resolve(predictions.map(result => ({
					__placeId: result.place_id,
					address: result.description,
					// See Geo::getGooglePlaceDetails() for `lat`, `lng`, and `parts`
				})));
			});
		});
	}

	/**
	 * Search using Apple MapKit
	 *
	 * @param {string} query
	 * @return {Promise<*>}
	 */
	searchApple (query) {
		return new Promise(resolve => {
			this.apple.Search.autocomplete(query, (err, data) => {
				resolve(data.results.slice(0, 5).map(result => ({
					address: result.displayLines.join(', '),
					lat: result.coordinate.latitude,
					lng: result.coordinate.longitude,
					// There's no way to get detailed address information from MapKit :(
					parts: new Parts(null, GeoService.AppleMapKit),
				})));
			});
			// TODO: Workout how to support preferred country
		});
	}

	/**
	 * Search using Here
	 *
	 * @param {string} query
	 * @return {Promise<*>}
	 */
	async searchHere (query) {
		const params = new URLSearchParams({
			app_id: this.token.appId,
			app_code: this.token.appCode,
			query,
			country: this.country ? this.country.toUpperCase() : '',
			maxresults: 5,
			language: this.locale,
		}).toString();

		const data = await fetch(
			'https://autocomplete.geocoder.api.here.com/6.2/suggest.json?' + params
		).then(res => res.json());

		if (!data.hasOwnProperty('suggestions'))
			return [];

		return data.suggestions.map(suggestion => ({
			__placeId: suggestion.locationId,
			address: suggestion.label,
			// See Geo::getHerePlaceDetails() for `lat`, `lng`, and `parts`
		}));
	}

	// Actions: Reverse
	// -------------------------------------------------------------------------

	/**
	 * Lookup the given lat/lng using Nominatim
	 *
	 * @param lat
	 * @param lng
	 * @param oldVal
	 * @return {Promise<{address: *, lng: *, parts: Parts, lat: *}>}
	 */
	async reverseNominatim ({ lat, lng }, oldVal) {
		const params = new URLSearchParams({
			lat,
			lon: lng,
			format: 'jsonv2',
			addressdetails: 1,
			'accept-language': this.locale,
		}).toString();

		const result = await fetch(
			'https://nominatim.openstreetmap.org/reverse?' + params
		).then(res => res.json());

		return {
			...oldVal,
			address: result.display_name,
			lat,
			lng,
			parts: new Parts({
				...result.address,
				type: result.type,
			}, GeoService.Nominatim),
		};
	}

	/**
	 * Lookup the given lat/lng using Mapbox
	 *
	 * @param lat
	 * @param lng
	 * @param oldVal
	 * @return {Promise<{address: *, lng: *, parts: Parts, lat: *}>}
	 */
	async reverseMapbox ({ lat, lng }, oldVal) {
		const params = new URLSearchParams({
			types: 'address,country,postcode,place,locality,district,neighborhood',
			limit: 1,
			access_token: this.token,
			language: this.locale,
		}).toString();

		const result = await fetch(
			'https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?' + params
		).then(res => res.json());

		const feature = result.features[0];

		return {
			...oldVal,
			address: feature.place_name,
			lat,
			lng,
			parts: new Parts(feature, GeoService.Mapbox),
		};
	}

	/**
	 * Lookup the given lat/lng using Google Maps
	 *
	 * @param latLng
	 * @param oldVal
	 * @return {Promise<any>}
	 */
	reverseGoogle (latLng, oldVal) {
		return new Promise(resolve => {
			this.google.geocoder.geocode({
				location: latLng,
			}, results => {
				const result = results[0];

				resolve({
					...oldVal,
					address: result.formatted_address,
					...latLng,
					parts: new PartsLegacy(
						result.address_components
					),
				});
			});
		});
	}

	/**
	 * Lookup the given lat/lng using Apple MapKit
	 *
	 * @param lat
	 * @param lng
	 * @param oldVal
	 * @return {Promise<any>}
	 */
	reverseApple ({ lat, lng }, oldVal) {
		return new Promise(resolve => {
			this.apple.Geocoder.reverseLookup(
				new this.apple.Coordinate(lat, lng),
				(err, data) => {
					const result = data.results[0];

					resolve({
						...oldVal,
						address: result.formattedAddress,
						lat,
						lng,
						// There's no way to get detailed address information from MapKit :(
						parts: new Parts(null, GeoService.AppleMapKit),
					});
				}
			);
		});
	}

	/**
	 * Lookup the given lat/lng using Here
	 *
	 * @param lat
	 * @param lng
	 * @param oldVal
	 * @return {Promise<{address: *, lng: *, parts: Parts, lat: *}>}
	 */
	async reverseHere ({ lat, lng }, oldVal) {
		const params = new URLSearchParams({
			app_id: this.token.appId,
			app_code: this.token.appCode,
			mode: 'retrieveAddresses',
			jsonattributes: 1,
			limit: 1,
			prox: `${lat},${lng},1`,
			language: this.locale,
		});

		const { response } = await fetch(
			'https://reverse.geocoder.api.here.com/6.2/reversegeocode.json?' + params
		).then(res => res.json());

		const { address } = response.view[0].result[0].location;

		return {
			...oldVal,
			address: address.label,
			lat,
			lng,
			parts: new Parts(address, GeoService.Here),
		};
	}

	// Helpers
	// =========================================================================

	/**
	 * Gets the details about the given place
	 *
	 * @param {string} placeId
	 * @param {Object} item
	 * @returns {Promise<*>}
	 */
	getGooglePlaceDetails (placeId, item) {
		return new Promise(resolve => {
			this.google.places.getDetails({
				placeId,
				fields: [
					'geometry',
					'address_component',
				],
			}, place => {
				resolve({
					...item,
					address: item.address,
					lat: place.geometry.location.lat(),
					lng: place.geometry.location.lng(),
					parts: new PartsLegacy(
						place.address_components
					),
				});
			});
		});
	}

	/**
	 * Gets the details about the given location
	 *
	 * @param {string} locationId
	 * @param {Object} item
	 * @return {Promise<*>}
	 */
	async getHerePlaceDetails (locationId, item) {
		const params = new URLSearchParams({
			app_id: this.token.appId,
			app_code: this.token.appCode,
			locationid: locationId,
			jsonattributes: 1,
			gen: 9,
			language: this.locale,
		}).toString();

		const data = await fetch(
			'https://geocoder.api.here.com/6.2/geocode.json?' + params
		).then(res => res.json());

		const place = data.response.view[0].result[0].location;

		return {
			...item,
			lat: place.displayPosition.latitude,
			lng: place.displayPosition.longitude,
			parts: new Parts(
				place.address,
				GeoService.Here
			),
		};
	}

}
