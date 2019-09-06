<template>
	<label :class="$style.label">
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
	.label {
		position: relative;
		display: block;

		border-right: 1px solid #DCE4EA;

		&:not(:last-child) {
			border-bottom: 1px solid #DCE4EA;
		}
	}

	.name {
		position: absolute;
		top: 9px;
		left: 12px;

		display: block;

		color: rgba(41, 50, 61, 0.41);
		font-size: 12px;
		font-weight: 500;
		letter-spacing: 0;
	}
</style>
