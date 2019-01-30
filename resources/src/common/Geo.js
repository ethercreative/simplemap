import GeoService from '../enums/GeoService';
import Parts from '../models/Parts';

export default class Geo {

	// Properties
	// =========================================================================

	service = null;
	token = null;

	google = { service: null, session: null };
	apple = { Search: null };

	// Constructor
	// =========================================================================

	constructor ({ service, token }) {
		// eslint-disable-next-line
		console.log(arguments);

		this.service = service;
		this.token = token;

		if (service === GeoService.GoogleMaps) {
			this.google = {
				service: new window.google.maps.places.AutocompleteService(),
				session: new window.google.maps.places.AutocompleteSessionToken(),
				places: new window.google.maps.places.PlacesService(
					document.createElement('div')
				),
			};
		} else if (service === GeoService.AppleMapKit) {
			window.mapkit.init({
				authorizationCallback: done => done(token),
			});

			this.apple = {
				Search: new window.mapkit.Search(),
				Geocoder: new window.mapkit.Geocoder(),
				Coordinate: window.mapkit.Coordinate,
			};
		}
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
		}).toString();

		const data = await fetch(
			'https://nominatim.openstreetmap.org/search?' + params
		).then(res => res.json());

		return data.map(result => ({
			address: result.display_name,
			lat: result.lat,
			lng: result.lng,
			parts: new Parts(result.address, GeoService.Nominatim),
		}));
	}

	/**
	 * Search using Mapbox
	 *
	 * @param {string} query
	 * @returns {Promise<*>}
	 */
	async searchMapbox (query) {
		const params = new URLSearchParams({
			types: 'address,country,postcode,place,locality,district,neighborhood',
			limit: 5,
			access_token: this.token,
		}).toString();

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
			}, predictions => {
				if (!predictions)
					return resolve([]);

				return resolve(predictions.map(result => ({
					__placeId: result.place_id,
					address: result.description,
					// See Search::getGooglePlaceDetails() for `lat`, `lng`, and `parts`
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
		});
	}

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
					address: item.address,
					lat: place.geometry.location.lat(),
					lng: place.geometry.location.lng(),
					parts: new Parts(
						place.address_components,
						GeoService.GoogleMaps
					),
				});
			});
		});
	}

}
