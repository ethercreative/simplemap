<template>
	<div :class="$style.grid">
		<Input
			:label="labels.number"
			:name="name + '[number]'"
			:value="value.parts.number"
		/>

		<Input
			:label="labels.address"
			:name="name + '[address]'"
			:value="value.parts.address"
		/>

		<Input
			:label="labels.city"
			:name="name + '[city]'"
			:value="value.parts.city"
		/>

		<Input
			:label="labels.postcode"
			:name="name + '[postcode]'"
			:value="value.parts.postcode"
		/>

		<Input
			:label="labels.county"
			:name="name + '[county]'"
			:value="value.parts.county"
		/>

		<Input
			:label="labels.state"
			:name="name + '[state]'"
			:value="value.parts.state"
		/>

		<Input
			:label="labels.country"
			:name="name + '[country]'"
			:value="value.parts.country"
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
			name: String,
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
	export default class Parts extends Vue {

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
