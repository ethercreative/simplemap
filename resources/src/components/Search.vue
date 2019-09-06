<template>
	<label :class="cls">
		<svg width="17" height="17" viewBox="0 0 17 17">
			<path fill="#29323D" d="M6.938 13.893c-3.805 0-6.917-3.112-6.917-6.917C.021 3.17 3.133.059 6.938.059c3.797 0 6.917 3.11 6.917 6.917a6.846 6.846 0 0 1-1.265 3.963l3.832 3.841c.246.246.36.572.36.906 0 .72-.536 1.292-1.274 1.292-.343 0-.677-.124-.923-.37l-3.85-3.858a6.874 6.874 0 0 1-3.797 1.143zm0-1.846c2.778 0 5.072-2.285 5.072-5.071 0-2.778-2.294-5.072-5.072-5.072-2.786 0-5.07 2.294-5.07 5.072 0 2.786 2.284 5.07 5.07 5.07z"/>
		</svg>
		<vue-autosuggest
			:suggestions="suggestions"
			:render-suggestion="renderSuggestion"
			:get-suggestion-value="getSuggestionValue"
			:should-render-suggestions="shouldRenderSuggestions"
			:input-props="inputProps"
			@selected="onSelected"
			@input="onInputChange"
			ref="self"
			:component-attr-class-autosuggest-results-container="$style.resultsWrap"
			:component-attr-class-autosuggest-results="$style.results"
		/>
	</label>
</template>

<script lang="jsx">
	import { VueAutosuggest } from 'vue-autosuggest';
	import { t } from '../filters/craft';
	import GeoService from '../enums/GeoService';
	import Geo from '../common/Geo';

	let to;

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
		},

		data () {
			return {
				shouldShow: false,
				suggestions: [{ data: [] }],
			};
		},

		computed: {
			cls () {
				const cls = [this.$style.wrap];

				if (this.isOpen)
					cls.push(this.$style.open);

				return cls;
			},

			inputProps () {
				const cls = [this.$style.input];

				return {
					class: cls,
					initialValue: this.initialValue,
					placeholder: t('Search for a location'),
				};
			},

			isOpen () {
				return this.suggestions[0].data.length > 0 && this.shouldShow;
			},

			suggestLengthShow () {
				return `${this.shouldShow}${this.suggestions[0].data.length}`;
			},
		},

		watch: {
			suggestLengthShow () {
				const list = this.$refs.self.$el.lastElementChild;

				if (!this.isOpen || !list.firstElementChild || window.matchMedia('(max-width: 767px)').matches) {
					this.$emit('open-offset', 0);
					return;
				}

				setTimeout(() => {
					this.$emit('open-offset', list.firstElementChild.getBoundingClientRect().height);
				});
			},
		},

		methods: {
			shouldRenderSuggestions (_, loading) {
				this.shouldShow = !loading;
				return () => true;
			},

			/**
			 * Fired on autocomplete input change
			 */
			onInputChange (text) {
				clearTimeout(to);
				to = setTimeout(async () => {
					const data       = await this.geo.search(text);
					this.suggestions = [{ data }];
				}, 500);
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
		position: relative;
		display: block;

		@media only screen and (max-width: 767px) {
			margin-right: 44px;
		}

		&, * {
			box-sizing: border-box;
		}

		> svg {
			position: absolute;
			top: 17px;
			left: 16px;
			pointer-events: none;
		}
	}
	.input {
		width: 100%;
		padding: 16px 0 15px 48px;

		font-size: 16px;

		appearance: none;
		background-color: #fff;
		border: none;
		border-bottom: 1px solid transparent;
		border-radius: 5px;
		box-shadow: 0 2px 15px 0 rgba(0, 0, 0, 0.20);

		.open & {
			border-radius: 5px 5px 0 0;
			border-bottom-color: #D2DBE1;
		}
	}
	.resultsWrap {
		position: absolute;
		top: 100%;
		left: -20px;

		width: calc(100% + 40px);
		height: 210px;
		padding: 0 20px 20px;

		overflow: hidden;
	}
	.results {
		position: relative;
		z-index: 2;
		width: 100%;
		padding: 7px 0;

		background-color: #fff;
		box-shadow: 0 5px 15px 0 rgba(0, 0, 0, 0.10);
		border-radius: 0 0 5px 5px;

		transform: translateY(calc(-100% - 15px));
		transition: transform 0.3s ease;

		.open & {
			transform: translateY(0);
		}

		li {
			padding: 7px 14px;
			transition: background-color 0.15s ease;

			&[class*="highlighted"] {
				background-color: #e4edf3;
			}
		}
	}
</style>
