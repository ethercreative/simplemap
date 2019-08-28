<template>
	<label>
		<span :class="$style.name">
			{{ label }}
		</span>
		<input
			:type="type"
			:name="name"
			:value="value"
			:disabled="disabled"
			class="text nicetext fullwidth"
			@input="$emit('input', $event)"
		/>
	</label>
</template>

<script lang="js">
	export default {
		props: {
			label: String,
			name: String,
			type: {
				type: String,
				default: 'text',
			},
			value: [String, Number],
			disabled: Boolean,
		},
	};
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
