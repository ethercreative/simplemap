<template>
	<div :class="cls" :style="styl">
		<Fragment v-if="showLatLng">
			<Input
				:label="labels.lat"
				:value="value.lat"
				@input="onInput('lat', $event)"
			/>
			<Input
				:label="labels.lng"
				:value="value.lng"
				@input="onInput('lng', $event)"
			/>
		</Fragment>

		<Input
			:class-name="$style.full"
			:label="labels.fullAddress"
			:value="value.address"
			@input="onInput('fullAddress', $event)"
			:disabled="hide"
		/>

		<Fragment v-if="!hide">
			<Input
				:label="labels.number"
				:value="value.parts.number"
				@input="onInput('number', $event)"
			/>

			<Input
				:label="labels.address"
				:value="value.parts.address"
				@input="onInput('address', $event)"
			/>

			<Input
				:label="labels.city"
				:value="value.parts.city"
				@input="onInput('city', $event)"
			/>

			<Input
				:label="labels.postcode"
				:value="value.parts.postcode"
				@input="onInput('postcode', $event)"
			/>

			<Input
				:label="labels.county"
				:value="value.parts.county"
				@input="onInput('county', $event)"
			/>

			<Input
				:label="labels.state"
				:value="value.parts.state"
				@input="onInput('state', $event)"
			/>

			<Input
				:label="labels.country"
				:value="value.parts.country"
				@input="onInput('country', $event)"
			/>
		</Fragment>
	</div>
</template>

<script lang="js">
	import Input from './Input';
	import Fragment from './Fragment';
	import { t } from '../filters/craft';

	export default {
		props: {
			value: {
				type: Object,
				default: () => ({
					address: '',
					lat: 0,
					lng: 0,
					parts: {},
				}),
			},
			showLatLng: Boolean,
			fullAddressDirty: Boolean,
			hide: Boolean,
			hasSearch: Boolean,
			hasMap: Boolean,
			size: String,
			resultsOpen: Number,
		},

		components: {
			Input,
			Fragment,
		},

		data () {
			return {
				labels: {
					fullAddress: t('Full Address'),
					number: t('Name / Number'),
					address: t('Street Address'),
					city: t('Town / City'),
					postcode: t('Postcode'),
					county: t('County'),
					state: t('State'),
					country: t('Country'),
					lat: t('Latitude'),
					lng: t('Longitude'),
				}
			};
		},

		computed: {
			cls () {
				const cls = [this.$style.grid];

				if (this.resultsOpen)
					cls.push(this.$style.fade);

				return cls;
			},

			styl () {
				return {
					transform: `translateY(${this.offset}px)`,
				};
			},
		},

		methods: {
			onInput (name, e) {
				this.$emit('changed', {
					name,
					value: e.target.value,
				});
			},
		},
	};
</script>

<style lang="less" module>
	.grid {
		display: grid;
		grid-template-columns: 1fr 1fr;

		background-color: #fff;
		border-radius: 5px;
		box-shadow: 0 2px 15px 0 rgba(0, 0, 0, 0.20);

		&:not(:first-child) {
			margin-top: 24px;
		}

		&.fade {
			opacity: 0;
		}

		@media only screen and (max-width: 1199px) {
			grid-template-columns: 1fr;

			label {
				border-right: none;
			}
		}

		@media only screen and (max-width: 767px) {
			margin-top: 200px !important;
		}

		&, * {
			box-sizing: border-box;
		}

		label:nth-child(odd):not(:first-child),
		label:not(.full) + label:nth-child(2) {
			border-right: none;
		}

		.full,
		label:last-child {
			grid-column: span 2;
			border-right: none;

			@media only screen and (max-width: 1199px) {
				grid-column: span 1;
			}
		}
	}
</style>
