<template>
	<img
		src="https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/0.53532,51.27064,15.1,0,0/700x400@2x?access_token=pk.eyJ1IjoiZXRoZXJjcmVhdGl2ZSIsImEiOiJjaWhjMDQ4cDUwMDg0dm9ranV3ZWZtY3B2In0.KQKSKysuha7MW19Q_cCJCA"
		alt=""
		:class="$style.map"
	/>
</template>

<script>
	export default {
		name: "Map",
	}
</script>

<style lang="less" module>
	.map {
		width: 100%;
		height: auto;
		vertical-align: middle;
	}
</style>