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
				@keydown="onInputKeyDown"
				@keypress="onInputKeyPress"
				ref="searchInput"
			/>
		</label>
		<div :class="{
			[$style.listWrap]: true,
			[$style.show]: hasFocus && results.length > 0,
		}">
			<ul :class="$style.list" ref="list">
				<li v-for="item in results">
					<button
						type="button"
						@focus="hasFocus = true"
						@blur="hasFocus = false"
						@keydown="onButtonKeyDown"
						@click="onButtonClick(item.id)"
						v-html="highlight(item)"
					></button>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import debounce from "../js/_helpers/debounce";
	import Google from "../js/autocomplete/Google";

	export default {
		name: "Search",

		props: {
			className: [Array, String],
		},

		data () {
			return {
				hasFocus: false,
				results: [],
				service: new Google(),
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
			},
		},

		methods: {

			// Actions
			// =================================================================

			highlight (item) {
				let output = item.text,
					offset = 0,
					hLen = "<strong></strong>".length;

				item.highlights.forEach(([o, l]) => {
					offset += o;

					const before = output.substr(0, offset)
						, target = output.substr(offset, l);

					offset += l;

					const after = output.substr(offset, output.length);

					offset += hLen - (o + l);

					output = `${before}<strong>${target}</strong>${after}`;
				});

				return output;
			},

			// Events
			// =================================================================

			onInputKeyPress: debounce(async function (e) {
				const query = e.target.value.trim();

				if (query === "") {
					this.results = [];
					return;
				}

				this.results = await this.service.search(query);
			}, 250),

			onInputKeyDown (e) {
				if (e.key !== "ArrowUp" && e.key !== "ArrowDown")
					return;

				e.preventDefault();

				if (e.key === "ArrowUp") {
					this.$refs.list.lastElementChild.firstElementChild.focus();
					return;
				}

				this.$refs.list.firstElementChild.firstElementChild.focus();
			},

			onButtonKeyDown (e) {
				if (e.key !== "ArrowUp" && e.key !== "ArrowDown")
					return;

				e.preventDefault();

				const li = e.target.parentNode;

				if (e.key === "ArrowUp") {
					if (li.previousElementSibling)
						li.previousElementSibling.firstElementChild.focus();
					else this.$refs.searchInput.focus();

					return;
				}

				if (li.nextElementSibling)
					li.nextElementSibling.firstElementChild.focus();
				else this.$refs.searchInput.focus();
			},

			async onButtonClick (id) {
				const data = await this.service.details(id);
				console.log(data);
			},
		},
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
		z-index: 2;

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

	.listWrap {
		position: absolute;
		z-index: 1;
		top: 100%;
		left: -7px;
		right: -7px;

		padding: 2px 7px 0;

		opacity: 0;
		pointer-events: none;
		overflow: hidden;

		transition: opacity 0.3s ease;

		&:hover,
		&.show {
			opacity: 1;
			pointer-events: all;

			.list {
				transform: translateY(0);
			}
		}
	}

	.list {
		background: #fff;
		border: 1px solid #E3E5E8;
		border-radius: 2px;
		box-shadow: 0 2px 6px 0 rgba(35, 36, 46, 0.08);

		transform: translateY(-10px);
		transition: transform 0.3s ease;

		li {
			&:not(:last-child) {
				border-bottom: 1px solid #f4f4f4;
			}
		}

		button {
			display: block;
			width: 100%;
			padding: 7px 10px;

			color: #4D4D4D;
			font-size: 14px;
			text-align: left;

			appearance: none;
			background: none;
			border: none;
			border-radius: 0;
			outline: none;
			cursor: pointer;

			transition: background-color 0.15s ease;

			&:focus,
			&:hover {
				background-color: #f4f4f4;
			}
		}
	}
</style>