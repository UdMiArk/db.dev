<template>
	<b-autocomplete
			:data="filteredItems"
			:disabled="disabled"
			:name="name"
			:readonly="readonly"
			:required="required"
			@blur="handleBlur"
			@focus="handleFocus"
			@select="handleSelect"

			field="name"
			keep-first
			open-on-focus
			v-model="searchString"
	/>
</template>

<script>
	export default {
		name: "AutocompleteSelectField",
		props: {
			items: {
				type: Array,
				required: true
			},
			value: {},
			valueField: String,
			outputField: String,
			valueFilter: Function,
			outputFilter: Function,
			searchFilter: Function,

			disabled: Boolean,
			readonly: Boolean,
			required: Boolean,
			name: String
		},
		data() {
			return {
				searchString: "",
				selectedIdx: null
			};
		},
		computed: {
			itemsData() {
				const
					items = this.items,
					values = [],
					labels = [],
					outputFilter = this.actualOutputFilter,
					valueFilter = this.actualValueFilter;

				for (let i = 0, iMax = items.length, item; i < iMax; i++) {
					item = items[i];
					values.push(valueFilter ? valueFilter(item) : item);
					labels.push(Object.freeze({
						id: i,
						name: outputFilter ? outputFilter(item) : item.toString()
					}));
				}

				return Object.freeze({
					values: Object.freeze(values),
					labels: Object.freeze(labels)
				});
			},
			filteredItems() {
				const searchString = this.searchString?.toUpperCase(),
					labels = this.itemsData.labels;
				if (!searchString) {
					return labels;
				}
				if (!(this.searchFilter || this.outputFilter)) {
					return Object.freeze(labels.filter((x) => x.name.toUpperCase().includes(searchString)));
				}
				const result = [],
					items = this.items,
					searchFilter = this.actualSearchFilter.bind(null, searchString);
				for (let i = 0, iMax = items.length; i < iMax; i++) {
					if (searchFilter(items[i])) {
						result.push(labels[i]);
					}
				}
				return Object.freeze(result);
			},
			actualSearchFilter() {
				if (this.searchFilter) {
					return this.searchFilter;
				} else if (this.outputField) {
					const field = this.outputField;
					return ((q, x) => x[field]?.toString().toUpperCase().includes(q));
				} else {
					return ((q, x) => x?.toString().toUpperCase().includes(q));
				}
			},
			actualOutputFilter() {
				if (this.outputFilter) {
					return this.outputFilter;
				} else if (this.outputField) {
					const field = this.outputField;
					return ((x) => x[field].toString());
				} else {
					return ((x) => x.toString());
				}
			},
			actualValueFilter() {
				if (this.valueFilter) {
					return this.valueField;
				} else if (this.valueField) {
					const field = this.valueField;
					return ((x) => x[field]);
				} else {
					return null;
				}
			}
		},
		methods: {
			handleSelect(option) {
				const idx = option ? option.id : null;
				this.selectedIdx = idx;
				if (!this.$_hasFocus || option) {
					this.searchString = option ? option.name : "";
					this.$nextTick(() => this.$children[0].checkHtml5Validity());
				}
				this.$emit("input", idx == null ? null : this.itemsData.values[idx]);
			},
			handleBlur(ev) {
				this.$_hasFocus = false;
				setTimeout(() => {
					if (this.selectedIdx == null) {
						this.searchString = "";
					}/* else {
						this.searchString = this.itemsData.labels[this.selectedIdx].name;
					}*/
					this.$nextTick(() => this.$children[0].checkHtml5Validity());
				}, 100);
			},
			handleFocus(ev) {
				this.$_hasFocus = true;
			},
			setValue(value) {
				const
					values = this.itemsData.values,
					currentIdx = this.selectedIdx,
					currentValue = currentIdx == null ? null : values[currentIdx];
				if (value !== currentValue) {
					const idx = value === undefined ? -1 : values.indexOf(value);
					if (idx === -1) {
						this.selectedIdx = null;
						this.searchString = "";
						if (value != null) {
							console.error("Unknown select value", value);
						}
					} else {
						this.selectedIdx = idx;
						this.searchString = this.itemsData.labels[idx].name;
					}
				}
			}
		},
		watch: {
			value(newValue) {
				this.setValue(newValue);
			}
		},
		created() {
			if (this.value !== undefined) {
				this.setValue(this.value);
			}
		}
	};
</script>

<style lang="scss">

</style>