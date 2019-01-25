<template>
	<div :class="$style.wrap" ref="self">
		<label>
			<input
				type="search"
				class="text nicetext fullwidth"
				:placeholder="'Search for a location'|t"
				@focus="onFocus"
				@blur="onBlur"
			/>
		</label>
		
		<ul v-if="isOpen && results.length" :class="$style.ul">
			<li v-for="result in results" :key="result.id">
				<button
					:class="$style.btn"
					type="button"
					@focus="onFocus"
					@blur="onBlur"
				>
					{{result.value}}
				</button>
			</li>
		</ul>
	</div>
</template>

<script lang="ts">
	import { Component, Vue } from 'vue-property-decorator';

	@Component({})
	export default class App extends Vue {

		// Properties
		// =====================================================================

		isOpen = false;

		results = [
			{ id: 1, value: 'Some location, some place, some where' },
			{ id: 2, value: 'Some other location, some other place, some where' },
			{ id: 3, value: 'No location, no place, no where' },
		];

		// Events
		// =====================================================================

		onFocus () {
			this.isOpen = true;
		}

		onBlur () {
			this.isOpen = this._hasFocus();
		}

		// Helpers
		// =====================================================================

		private _hasFocus (): boolean {
			const self = this.$refs.self as Element;

			return self.contains(document.activeElement);
		}

	}
</script>

<style lang="less" module>
	.wrap {
		position: relative;
	}

	.ul {
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;

		display: block;
		margin-top: 5px;

		list-style: none;

		background-color: #fff;
		border: 1px solid rgba(0, 0, 20, 0.1);
		border-radius: 2px;
		box-shadow: 0 3px 10px fade(#000, 5%);

		li:not(:last-child) {
			border-bottom: 1px solid rgba(0, 0, 20, 0.1);
		}
	}

	.btn {
		display: block;
		width: 100%;
		padding: 10px 7px;

		color: #52606f;
		font-size: 14px;
		font-family: system-ui, BlinkMacSystemFont, -apple-system, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
		text-align: left;

		appearance: none;
		background: none;
		border: none;
		border-radius: 0;
		cursor: pointer;

		transition: background-color 0.15s ease;

		&:focus,
		&:hover {
			outline: none;
			background: #f4f4f4;
		}
	}
</style>
