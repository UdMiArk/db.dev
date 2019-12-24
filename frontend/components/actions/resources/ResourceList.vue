<template>
	<DataList :filters="actualFilters" :page="page - 1" :preventUpdate="additionalDataLoading" :sorting="sorting" source="resources/list">
		<template #header>
			<div>
				<b-button :to="{name: 'resourceCreate'}" class="is-pulled-right" tag="router-link">Добавить ресурс</b-button>
				<input class="input is-inline mr-sm" placeholder="Тип" type="text" v-model.lazy="manualFiltersType"/>
				<input class="input is-inline mr-sm" placeholder="Создатель" type="text" v-if="showUser" v-model.lazy="manualFiltersUser"/>
				<input class="input is-inline mr-sm" placeholder="Объект Продвижения" type="text" v-if="showProduct" v-model.lazy="manualFiltersProduct"/>
				<b-button @click="manualFilters = null" icon-left="close" title="Очистить фильтры" v-if="manualFilters"/>
			</div>
		</template>
		<template #default="{items, total, pageSize: actualPageSize, loading, error, reloadData}">
			<ResourcesTable
					:currentPage="page"
					:data="items"
					:loading="loading || additionalDataLoading"
					:perPage="actualPageSize"
					:total="total"
					:withProduct="showProduct"
					:withStatus="showStatus"
					:withUser="showUser"
					@itemActivated="handleItemActivation"
					@pageChange="page = $event"
					@sort="sorting = $event"
					backendPagination
					backendSorting
					paginated
			>
				<template #empty>
					<div class="box has-background-warning" v-if="error">
						<b-button @click="reloadData" class="is-pulled-right" icon-left="reload" size="is-small" title="Перезагрузить" type="is-primary"/>
						{{error.message || error}}
					</div>
				</template>
			</ResourcesTable>
		</template>
	</DataList>
</template>

<script>
	import ResourcesTable from "@components/resources/ResourcesTable";
	import DataList from "@components/actions/DataList";

	const DEFAULT_PAGE = 1;

	export default {
		name: "ResourceList",
		components: {DataList, ResourcesTable},
		props: {
			additionalFilters: Object,
			showUser: Boolean,
			showProduct: Boolean,
			showStatus: Boolean,
			defaultActivation: Boolean,
			additionalDataLoading: Boolean
		},
		data() {
			return {
				sorting: null,
				manualFilters: null,
				page: DEFAULT_PAGE
			};
		},
		computed: {
			manualFiltersUser: {
				get() {
					return this.manualFilters?.user || "";
				},
				set(val) {
					this.setFilterValue("user", val);
				}
			},
			manualFiltersProduct: {
				get() {
					return this.manualFilters?.product || "";
				},
				set(val) {
					this.setFilterValue("product", val);
				}
			},
			manualFiltersType: {
				get() {
					return this.manualFilters?.type || null;
				},
				set(val) {
					this.setFilterValue("type", val);
				}
			},
			actualFilters() {
				const result = {};
				if (this.manualFilters) {
					Object.assign(result, this.manualFilters);
				}
				if (this.additionalFilters) {
					Object.assign(result, this.additionalFilters);
				}
				if (result.product_id) {
					delete result.product;
				}
				if (result.user_id) {
					delete result.user;
				}
				if (result.type_id) {
					delete result.type;
				}
				return Object.keys(result).length ? Object.freeze(result) : null;
			}
		},
		methods: {
			setFilterValue(prop, val) {
				const newFiler = this.manualFilters ? Object.assign({}, this.manualFilters) : {};
				if ((val || "") !== (newFiler[prop] || "")) {
					if (val) {
						newFiler[prop] = val;
					} else {
						delete newFiler[prop];
					}
					this.manualFilters = Object.keys(newFiler).length ? Object.freeze(newFiler) : null;
				}
			},
			handleItemActivation(item) {
				if (this.defaultActivation) {
					this.$router.push({name: "resourceView", params: {qPk: item.__id.toString()}});
				} else {
					this.$emit("itemActivated", item);
				}
			}
		},
		watch: {
			actualFilters() {
				if (this.page !== DEFAULT_PAGE) {
					this.page = DEFAULT_PAGE;
				}
			}
		}
	};
</script>

<style lang="scss">

</style>