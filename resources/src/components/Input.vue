<template>
	<Label :label="label" :class="className">
		<input
			:class="$style.input"
			:type="type"
			:name="name"
			:value="value"
			:disabled="disabled"
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
		width: 100%;
		padding: 29px 0 10px;

		color: #29323D;
		font-size: 15px;
		letter-spacing: 0;
		text-indent: 12px;

		appearance: none;
		-moz-appearance: none;
		-webkit-appearance: none;
		background: none;
		border: none;
		border-radius: 0;

		&:disabled {
			opacity: 0.5;
		}
	}
</style>
