<template>
	<div :class="$style.grid">
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
	</div>
</template>

<script lang="js">
	import { Component, Vue } from 'vue-property-decorator';
	import Input from './Input';
	import { t } from '../filters/craft';

	@Component({
		components: {
			Input,
		},
		props: {
			value: {
				type: Object,
				default: {
					address: '',
					lat: 0,
					lng: 0,
					parts: {},
				},
			},
		}
	})
	export default class Address extends Vue {

		// Properties
		// =====================================================================

		labels = {
			number: t('Name / Number'),
			address: t('Street Address'),
			city: t('Town / City'),
			postcode: t('Postcode'),
			county: t('County'),
			state: t('State'),
			country: t('Country'),
		};

		// Events
		// =====================================================================

		onInput (name, e) {
			this.$emit('changed', {
				name,
				value: e.target.value,
			});
		}

	}
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

		label:last-child:nth-child(odd) {
			grid-column: span 2;
		}
	}
</style>
