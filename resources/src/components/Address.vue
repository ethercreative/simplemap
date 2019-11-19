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

		<div :class="[$style.full, $style.row]">
			<Input
				:label="labels.fullAddress"
				:value="value.address"
				@input="onInput('fullAddress', $event)"
				:disabled="hide"
			/>
			<button
				:class="$style.btn"
				@click="onClear()"
				type="button"
				@mouseenter="onDeleteEnter"
				@mouseleave="onDeleteLeave"
				:title="labels.clear"
			>
				<svg width="14" height="14" viewBox="0 0 14 14">
					<path fill="#29323D" d="M7 14c-3.832 0-7-3.167-7-6.997C0 3.167 3.162 0 6.993 0 10.825 0 14 3.167 14 7.003 14 10.833 10.832 14 7 14zM4.65 9.994a.65.65 0 0 0 .468-.19l1.875-1.88 1.889 1.88c.115.116.27.19.46.19.366 0 .65-.29.65-.65a.674.674 0 0 0-.19-.46l-1.888-1.88 1.889-1.895a.581.581 0 0 0 .196-.447.64.64 0 0 0-.65-.643.59.59 0 0 0-.453.19L6.993 6.097 5.104 4.216a.607.607 0 0 0-.453-.19.64.64 0 0 0-.65.643c0 .17.074.331.196.447L6.08 7.003 4.197 8.898a.608.608 0 0 0-.196.447c0 .358.291.65.65.65z"/>
				</svg>
			</button>
		</div>

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
			openOffset: Number,
		},

		components: {
			Input,
			Fragment,
		},

		data () {
			return {
				hoverDelete: false,
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
					clear: t('Clear address'),
				}
			};
		},

		computed: {
			cls () {
				const cls = [this.$style.grid];

				if (this.hasMap && this.openOffset > 0)
					cls.push(this.$style.fade);

				if (!this.hasSearch)
					cls.push(this.$style['no-search']);

				if (!this.hasMap)
					cls.push(this.$style['no-map']);

				if (this.hoverDelete)
					cls.push(this.$style.delete);

				if (this.hasValue)
					cls.push(this.$style['show-clear']);

				return cls;
			},

			styl () {
				if (!this.hasMap)
					return {};

				return {
					transform: `translateY(${this.openOffset}px)`,
				};
			},

			hasValue () {
				return this.value.address !== null;
			},
		},

		methods: {
			onInput (name, e) {
				this.$emit('changed', {
					name,
					value: e.target.value,
				});
			},

			onClear () {
				this.$emit('clear');
			},

			onDeleteEnter () {
				this.hoverDelete = true;
			},

			onDeleteLeave () {
				this.hoverDelete = false;
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

		overflow: hidden;
		transition: transform 0.3s ease, opacity 0.3s ease;

		&:not(:first-child) {
			margin-top: 24px;
		}

		&.fade {
			opacity: 0.8;
		}

		&.no-map {
			box-shadow: none;
			border: 1px solid #DCE4EA;
		}

		@media only screen and (max-width: 1199px) {
			grid-template-columns: 1fr;

			label {
				border-right: none;
			}
		}

		@media only screen and (max-width: 767px) {
			&:not(.no-map):not(.no-search) {
				margin-top: 200px !important;
			}
			&:not(.no-map).no-search {
				margin-top: 260px !important;
			}
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
	.row {
		display: flex;

		&:last-child > * {
			border-bottom: none;
		}

		label {
			flex-grow: 1;
			border-right: none;
		}
	}
	.btn {
		appearance: none;
		background: none;
		border: none;
		border-bottom: 1px solid #DCE4EA;
		border-radius: 0 5px 0 0;
		cursor: pointer;

		pointer-events: none;

		svg {
			opacity: 0.5;

			transform: translateX(200%);
			transition: transform 0.3s ease, opacity 0.15s ease;
		}

		path {
			transition: fill 0.15s ease;
		}

		.show-clear & {
			pointer-events: auto;

			svg {
				transform: translateX(0);
			}
		}

		&:hover {
			svg {
				opacity: 1;
			}
			path {
				fill: #F22C26;
			}
		}
	}
	.delete input {
		color: #F22C26;
	}
</style>
