<template>
	<div>
		<div class="box is-narrow" v-if="$slots.header">
			<slot name="header"/>
		</div>
		<slot
				:error="error"
				:items="items"
				:loading="isLoading"
				:pageSize="pageSize"
				:reloadData="reloadData"
				:total="total"
		/>
		<div v-if="$slots.footer">
			<slot name="footer"/>
		</div>
	</div>
</template>

<script>
	import {deepFreeze} from "@/plugins/object";
	import {jsonFetchHandler} from "@plugins/api";

	export default {
		name: "DataList",
		props: {
			source: {
				required: true,
				type: String
			},
			sorting: String,
			filters: Object,
			page: Number,
			pageSize: Number,
			additionalSourceParams: Object,
			keepDataOnError: Boolean
		},
		data() {
			return {
				items: Object.freeze([]),
				total: null,
				isLoading: true,
				error: null
			};
		},
		computed: {
			sourceParams() {
				const result = {};
				if (this.additionalSourceParams) {
					Object.apply(result, this.additionalSourceParams);
				}
				if (this.page) {
					result.page = this.page;
				}
				if (this.pageSize) {
					result.pageSize = this.pageSize;
				}
				if (this.sorting) {
					result.sorting = this.sorting;
				}
				if (this.filters) {
					result.filters = JSON.stringify(this.filters);
				}
				if (Object.keys(result).length) {
					return Object.freeze(result);
				}
				return undefined;
			}
		},
		methods: {
			reloadData() {
				this.error = null;
				this.isLoading = true;
				const request = this.$lastRequest = this.$apiGet(this.source, this.sourceParams);

				return request
					.then(response => {
						if (request === this.$lastRequest) {
							return jsonFetchHandler(response).then(this.handleResponse.bind(this));
						}
					})
					.catch(err => {
						if (request === this.$lastRequest) {
							this.handleResponseError(err);
						}
					})
					.finally(() => {
						if (this.$lastRequest === request) {
							this.isLoading = false;
							this.$lastRequest = null;
						}
					});
			},
			handleResponse({data: {items, count}}) {
				this.items = deepFreeze(items);
				this.total = count;
			},
			handleResponseError(err) {
				if (!this.keepDataOnError) {
					this.items = Object.freeze([]);
				}
				this.error = err;
			}
		},
		watch: {
			sourceParams() {
				this.reloadData();
			}
		},
		created() {
			this.reloadData();
		}
	};
</script>

<style lang="scss">

</style>