<template>
	<vue-autosuggest
		:suggestions="suggestions"
		:render-suggestion="renderSuggestion"
		:get-suggestion-value="getSuggestionValue"
		:input-props="inputProps"
		@selected="onSelected"
	/>
</template>

<script lang="jsx">
	import { Component, Vue } from 'vue-property-decorator';
	import { VueAutosuggest } from 'vue-autosuggest';
	import { t } from '../filters/craft';
	import Parts from '../models/Parts';
	import GeoService from '../enums/GeoService';

	@Component({
		components: {
			VueAutosuggest,
		},
		props: {
			service: String,
			token: String,
			defaultValue: String,
		},
	})
	export default class Search extends Vue {

		// Properties
		// =====================================================================

		suggestions = [{ data: [] }];

		google = { service: null, session: null };
		apple = { Search: null };

		// Getters
		// =====================================================================

		get inputProps () {
			return {
				onInputChange: this.onInputChange(),
				class: 'text nicetext fullwidth',
				placeholder: t('Search for a location'),
				initialValue: this.initialValue,
			};
		}

		// Vue
		// =====================================================================

		mounted () {
			if (this.service === GeoService.GoogleMaps) {
				this.google = {
					service: new google.maps.places.AutocompleteService(),
					session: new google.maps.places.AutocompleteSessionToken(),
					places: new google.maps.places.PlacesService(
						document.createElement('div')
					),
				};
			} else if (this.service === GeoService.AppleMapKit) {
				this.apple = {
					Search: new window.mapkit.Search(),
				};
			}
		}

		// Actions
		// =====================================================================

		/**
		 * Run the search
		 *
		 * @param {string} text
		 * @returns {Promise<void>}
		 */
		async search (text) {
			if (!text || text.trim() === '') {
				this.suggestions = [{ data: [] }];
				return;
			}

			text = encodeURI(text);

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

			this.suggestions = [{ data: suggestions }];
		}

		/**
		 * Search using Nominatim
		 *
		 * @param {string} query
		 * @returns {Promise<*>}
		 */
		async searchNominatim (query) {
			const params = new URLSearchParams({
				format: 'jsonv2',
				limit: 5,
				addressdetails: 1,
				q: query,
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
				parts: new Parts(result.context, GeoService.Mapbox),
			}));
		}

		/**
		 * Search using Google Places
		 *
		 * @param {string} query
		 * @returns {Promise<*>}
		 */
		async searchGoogle (query) {
			return new Promise(resolve => {
				this.google.service.getPlacePredictions({
					input: decodeURI(query),
					sessionToken: this.google.session,
				}, predictions => {
					resolve(predictions.map(result => ({
						__placeId: result.place_id,
						address: result.description,
						// See Search::getGooglePlaceDetails() for the below:
						// lat
						// lng
						// parts
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
		async searchApple (query) {
			return new Promise(resolve => {
				this.apple.Search.autocomplete(decodeURI(query), (err, data) => {
					resolve(data.results.slice(0, 5).map(result => ({
						address: result.displayLines.join(', '),
						lat: result.coordinate.latitude,
						lng: result.coordinate.longitude,
						parts: new Parts(null, GeoService.AppleMapKit),
					})));
				});
			});
		}

		// Events
		// =====================================================================

		/**
		 * Fired on autocomplete input change
		 */
		onInputChange () {
			const that = this;
			let to = null;

			return function (text) {
				clearTimeout(to);
				to = setTimeout(() => {
					that.search(text);
				}, 500);
			};
		}

		/**
		 * When an item from the autocomplete is selected
		 *
		 * @param item
		 */
		async onSelected ({ item }) {
			if (this.service === GeoService.GoogleMaps)
				item = await this.getGooglePlaceDetails(item.__placeId, item);

			this.$emit('selected', item);
		}

		// Render
		// =====================================================================

		/**
		 * Renders the given item in the autocomplete dropdown
		 *
		 * TODO: Highlight search term?
		 *
		 * @param item
		 * @returns {*|string}
		 */
		renderSuggestion ({ item }) {
			return item.address;
		}

		// Helpers
		// =====================================================================

		/**
		 * Converts the given autocomplete suggestion to a string
		 * (to auto-fill the input)
		 *
		 * @param suggestion
		 * @returns {string}
		 */
		getSuggestionValue (suggestion) {
			return suggestion.item.address;
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
</script>

<style lang="less" module>
</style>
