<template>
	<label>
		<span :class="$style.name">
			{{ label }}
		</span>
		<input
			:type="type"
			:name="name"
			:value="value"
			class="text nicetext fullwidth"
		/>
	</label>
</template>

<script lang="js">
	import { Component, Vue } from 'vue-property-decorator';

	@Component({
		props: {
			label: String,
			name: String,
			type: {
				type: String,
				default: 'text',
			},
			value: [String, Number],
		}
	})
	export default class Input extends Vue {
		//
	}
</script>

<style lang="less" module>
	.name {
		display: block;
		margin-bottom: 3px;

		color: #a1a1a1;
		font-size: 11px;
		text-transform: uppercase;
	}
</style>
