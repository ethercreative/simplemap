<template>
	<div :class="cls">
		<label :class="$style.label">
			<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
				<path transform="translate(1 1)" d="M11.6363636 5.81890909C11.6363636 2.60581818 9.03127273 0 5.81818182 0 2.60509091 0 0 2.60581818 0 5.81890909 0 9.03127273 2.60509091 11.6363636 5.81818182 11.6363636 9.03127273 11.6363636 11.6363636 9.03127273 11.6363636 5.81890909zM9.93178182 9.93149091L16.0001455 15.9998545"></path>
			</svg>
			<input
				type="search"
				placeholder="Search for a location or business"
				:class="$style.input"
				@focus="hasFocus = true"
				@blur="hasFocus = false"
			/>
		</label>
	</div>
</template>

<script>
	export default {
		name: "search",

		props: {
			className: [Array, String],
		},

		data () {
			return {
				hasFocus: false,
			};
		},

		computed: {
			cls () {
				const cls = [this.$style.wrap];

				if (this.hasFocus)
					cls.push(this.$style.focus);

				if (Array.isArray(this.className))
					cls.concat(this.className);
				else if (this.className)
					cls.push(this.className);

				return cls;
			}
		}
	}
</script>

<style lang="less" module>
	.wrap {
		background: #fff;
		border: 1px solid #E3E5E8;
		border-radius: 2px;
		box-shadow: 0 2px 6px 0 rgba(35, 36, 46, 0.08);

		transition: border-color 0.15s ease;

		&.focus {
			border-color: #0d99f2;
		}
	}

	.label {
		position: relative;

		svg {
			position: absolute;
			top: 50%;
			left: 15px;

			transform: translateY(-50%);
		}

		path {
			stroke: #A5A5A5;
			stroke-linecap: round;
			stroke-linejoin: round;
			stroke-width: 2;
			fill: none;
		}
	}

	.input {
		width: 100%;
		padding: 15px 0;

		color: #4D4D4D;
		text-indent: 48px;

		appearance: none;
		background: none;
		border: none;
		border-radius: 0;

		&::placeholder {
			color: #9C9C9C;
		}
	}
</style>