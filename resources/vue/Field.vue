<template>
	<div :class="$style.wrap">
		<search
			:class-name="$style.search"
			api-key="AIzaSyBYnMhGXLUVqhmVnwjaW1qEJYJEjAx0ohk"
		/>
		<x-map />
	</div>
</template>

<script>
	import Search from "./Search";
	import XMap from "./Map";

	export default {
		name: "Field",
		components: { Search, XMap },
	}
</script>

<style lang="less" module>
	.wrap {
		position: relative;

		border: 1px solid #E3E5E8;
		border-radius: 2px;

		overflow: hidden;
	}

	.search {
		position: absolute;
		top: 10px;
		left: 10px;

		width: calc(100% - 20px);
	}
</style>