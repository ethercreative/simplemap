<template>
	<div :class="cls">
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

			<hr />
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

				if (this.hasMap && this.size === 'medium')
					cls.push(this.$style.medium);

				if (!this.hasSearch && !this.hasMap)
					cls.push(this.$style.alone);

				return cls;
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
		grid-gap: 7px 14px;
		padding: 12px 14px 18px;

		background-color: #f9fbfc;
		border: 1px solid rgba(0, 0, 20, 0.1);
		border-top: none;
		border-radius: 0 0 2px 2px;

		&.medium {
			border-radius: 0 2px 2px 0;
			border-left: none;
			border-top: 1px solid rgba(0, 0, 20, 0.1);
		}

		&.alone {
			border-radius: 2px;
			border-top: 1px solid rgba(0, 0, 20, 0.1);
		}

		@media only screen and (max-width: 998px) {
			grid-template-columns: 1fr;
		}

		.full,
		label:last-child,
		hr {
			grid-column: span 2;

			@media only screen and (max-width: 998px) {
				grid-column: span 1;
			}
		}

		hr {
			margin: 11px -14px 6px;
			opacity: 0.25;
		}
	}
</style>
