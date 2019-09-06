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
		display: block;
		padding: 9px 0 9px 12px;

		border-right: 1px solid #DCE4EA;

		&:not(:last-child) {
			border-bottom: 1px solid #DCE4EA;
		}
	}

	.name {
		display: block;
		margin-bottom: 1px;

		color: rgba(41, 50, 61, 0.41);
		font-size: 12px;
		font-weight: 500;
		letter-spacing: 0;
	}
</style>
