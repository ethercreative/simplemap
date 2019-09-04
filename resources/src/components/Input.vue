<template>
	<Label :label="label" :class="className">
		<input
			:class="$style.input"
			:type="type"
			:name="name"
			:value="value"
			:disabled="disabled"
			class="text nicetext fullwidth"
			@input="$emit('input', $event)"
		/>
	</Label>
</template>

<script lang="js">
	import Label from './Label';

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
			className: Boolean,
		},

		components: {
			Label,
		},
	};
</script>

<style lang="less" module>
	.input {
		&:disabled {
			background-color: transparent;
		}
	}
</style>
