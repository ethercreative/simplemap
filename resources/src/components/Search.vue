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
	import GeoService from '../enums/GeoService';
	import Geo from '../common/Geo';

	@Component({
		components: {
			VueAutosuggest,
		},
		props: {
			geo: Geo,
			name: String,
			service: String,
			defaultValue: String,
		},
	})
	export default class Search extends Vue {

		// Properties
		// =====================================================================

		suggestions = [{ data: [] }];

		// Getters
		// =====================================================================

		get inputProps () {
			return {
				onInputChange: this.onInputChange(),
				class: 'text nicetext fullwidth',
				placeholder: t('Search for a location'),
				initialValue: this.initialValue,
				name: this.name + '[address]',
			};
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
				to = setTimeout(async () => {
					const data = await that.geo.search(text);
					that.suggestions = [{ data }];
				}, 500);
			};
		}

		/**
		 * When an item from the autocomplete is selected
		 *
		 * @param selected
		 */
		async onSelected (selected) {
			if (!selected)
				return;

			let item = selected.item;

			if (this.service === GeoService.GoogleMaps)
				item = await this.geo.getGooglePlaceDetails(item.__placeId, item);

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
		renderSuggestion = ({ item }) => item.address;

		// Helpers
		// =====================================================================

		/**
		 * Converts the given autocomplete suggestion to a string
		 * (to auto-fill the input)
		 *
		 * @param suggestion
		 * @returns {string}
		 */
		getSuggestionValue = suggestion => suggestion.item.address;

	}
</script>

<style lang="less" module>
</style>
