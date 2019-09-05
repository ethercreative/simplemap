<template>
	<div :class="cls">
		<Label :label="labels.search">
			<vue-autosuggest
				:suggestions="suggestions"
				:render-suggestion="renderSuggestion"
				:get-suggestion-value="getSuggestionValue"
				:input-props="inputProps"
				@selected="onSelected"
				ref="self"
			/>
		</Label>
		<button
			:class="['btn', $style.btn]"
			@click="onClear()"
			type="button"
		>
			{{labels.clear}}
		</button>
	</div>
</template>

<script lang="jsx">
	import { VueAutosuggest } from 'vue-autosuggest';
	import { t } from '../filters/craft';
	import GeoService from '../enums/GeoService';
	import Geo from '../common/Geo';
	import Label from './Label';

	export default {
		props: {
			geo: Geo,
			service: String,
			defaultValue: String,
			hasMap: Boolean,
			size: String,
		},

		components: {
			VueAutosuggest,
			Label,
		},

		data () {
			return {
				suggestions: [{ data: [] }],
				labels: {
					search: t('Search for a location'),
					clear: t('Clear address'),
				},
			};
		},

		computed: {
			cls () {
				const cls = [this.$style.wrap];

				if (!this.hasMap)
					cls.push(this.$style.addr);
				else if (this.size === 'medium')
					cls.push(this.$style.medium);

				return cls;
			},

			inputProps () {
				return {
					onInputChange: this.onInputChange(),
					class: 'text nicetext fullwidth',
					initialValue: this.initialValue,
				};
			},
		},

		methods: {
			onClear () {
				this.$emit('clear');
			},

			/**
			 * Fired on autocomplete input change
			 */
			onInputChange () {
				const that = this;
				let to     = null;

				return function (text) {
					clearTimeout(to);
					to = setTimeout(async () => {
						const data       = await that.geo.search(text);
						that.suggestions = [{ data }];
					}, 500);
				};
			},

			/**
			 * When an item from the autocomplete is selected
			 *
			 * @param selected
			 */
			async onSelected (selected) {
				if (!selected)
					return;

				let item = selected.item;

				// eslint-disable-next-line default-case
				switch (this.service) {
					case GeoService.GoogleMaps:
						item = await this.geo.getGooglePlaceDetails(item.__placeId, item);
						break;
					case GeoService.Here:
						item = await this.geo.getHerePlaceDetails(item.__placeId, item);
						break;
				}

				this.$emit('selected', item);
			},

			/**
			 * Renders the given item in the autocomplete dropdown
			 *
			 * TODO: Highlight search term?
			 *
			 * @param item
			 * @returns {*|string}
			 */
			renderSuggestion: ({ item }) => item.address,

			/**
			 * Converts the given autocomplete suggestion to a string
			 * (to auto-fill the input)
			 *
			 * @param suggestion
			 * @returns {string}
			 */
			getSuggestionValue: suggestion => suggestion.item.address,
		},
	};
</script>

<style lang="less" module>
	.wrap {
		display: flex;
		align-items: flex-end;
		padding: 12px 14px 18px;

		background-color: #f9fbfc;
		border: 1px solid rgba(0, 0, 20, 0.1);
		border-bottom: none;
		border-radius: 2px 2px 0 0;

		label {
			width: 100%;
		}

		&.addr {
			border-bottom: 1px solid rgba(0, 0, 20, 0.025);
		}

		&.medium {
			border-radius: 2px 0 0 0;
		}
	}

	.btn {
		margin-left: 14px;
		font-size: 14px;
	}
</style>
