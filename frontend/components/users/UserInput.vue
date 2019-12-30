<template>
	<span class="input" v-if="readonly">{{value | user}}</span>
	<b-taginput
			:allowNew="false"
			:autocomplete="autocomplete"
			:data="filteredItems"
			:disabled="disabled"

			:loading="loading"
			:type="errorMsg ? 'is-danger' : undefined"
			@typing="handleTyping"
			field="name"
			maxtags="1"

			v-else


			v-model="model"
	>
		<template #default="{option}">
			{{option.name}}
		</template>
	</b-taginput>
</template>

<script>
	import {deepFreeze} from "@plugins/object";

	export default {
		name: "UserInput",
		props: {
			value: Object,
			source: [Object, Array],
			placeholder: {
				type: String,
				default: "Введите имя пользователя"
			},
			disabled: Boolean,
			readonly: Boolean
		},
		data() {
			return {
				availableItems: null,
				searchString: null,
				errorMsg: null
			};
		},
		computed: {
			model: {
				get() {
					let value = this.value;
					if (value && this.availableItems) {
						const valueId = value.__id;
						value = this.availableItems.find(x => x.__id === valueId) || value;
					}
					return value ? [value] : [];
				},
				set(val) {
					this.$emit("input", val?.[0] || null);
				}
			},
			loading() {
				return !this.availableItems;
			},
			autocomplete() {
				return !this.disabled && this.availableItems && true || false;
			},
			filteredItems() {
				if (this.availableItems && this.searchString) {
					const searchString = this.searchString.toLowerCase();
					return Object.freeze(this.availableItems.filter(x => x.name.toLowerCase().includes(searchString)));
				}
				return this.availableItems;
			}
		},
		methods: {
			refreshSource() {
				const source = this.source;
				if (!source) {
					this.availableItems = null;
				} else if (Array.isArray(source)) {
					this.availableItems = source;
				} else if (source.api) {
					this.reloadApiSource();
				} else {
					console.error("Unknown source type", source);
				}
			},
			reloadApiSource() {
				const source = this.source;
				if (!source.api) {
					console.error("Bad api source data", source);
					return;
				}
				this.availableItems = null;
				this.errorMsg = null;
				this.$apiGetJ(source.api, source.query).then(({data}) => {
					this.availableItems = deepFreeze(data);
				}).catch((err) => {
					this.errorMsg = err?.message || err;
					this.$handleErrorWithBuefy(err);
				});
			},

			handleTyping(searchString) {
				this.searchString = searchString;
			}
		},
		watch: {
			source() {
				this.refreshSource();
			}
		},
		created() {
			this.refreshSource();
		}
	};
</script>
