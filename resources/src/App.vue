<template>
	<div>
		<Search />
	</div>
</template>

<script lang="ts">
	import { Component, Vue } from 'vue-property-decorator';
	import Search from './components/Search.vue';

	@Component({
		components: {
			Search,
		},
	})
	export default class App extends Vue {
	}
</script>

<style lang="less" module>
</style>
