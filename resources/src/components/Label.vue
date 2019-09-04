<template>
	<label>
		<span :class="$style.name">
			{{ label }}
		</span>
		<slot></slot>
	</label>
</template>

<script lang="js">
	export default {
		props: {
			label: String,
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
