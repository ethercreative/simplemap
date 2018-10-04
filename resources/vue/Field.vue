<template>
	<div :class="$style.wrap">
		<search :class-name="$style.search" />

		<img
			src="https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/0.53532,51.27064,15.1,0,0/700x400@2x?access_token=pk.eyJ1IjoiZXRoZXJjcmVhdGl2ZSIsImEiOiJjaWhjMDQ4cDUwMDg0dm9ranV3ZWZtY3B2In0.KQKSKysuha7MW19Q_cCJCA"
			alt=""
		/>
	</div>
</template>

<script>
	import Search from "./Search";

	export default {
		name: "field",
		components: { Search },
	}
</script>

<style lang="less" module>
	.wrap {
		position: relative;

		border: 1px solid #E3E5E8;
		border-radius: 2px;

		overflow: hidden;

		img {
			width: 100%;
			height: auto;
			vertical-align: middle;
		}
	}

	.search {
		position: absolute;
		top: 10px;
		left: 10px;

		width: calc(100% - 20px);
	}
</style>