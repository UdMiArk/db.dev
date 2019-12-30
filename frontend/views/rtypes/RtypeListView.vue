<template>
	<BaseViewContainer>
		<DataList :filters="actualFilters" :page="page - 1" :pageSize="50" :sorting="sorting" source="resource-types/list">
			<template #header>
				<div>
					<b-button :to="{name: 'rtypeCreate'}" class="is-pulled-right" tag="router-link">Добавить тип</b-button>
					<input class="input is-inline mr-sm" placeholder="Имя" type="text" v-model.lazy="manualFiltersName"/>
					<b-button @click="manualFilters = null" icon-left="close" title="Очистить фильтры" v-if="manualFilters"/>
				</div>
			</template>
			<template #default="{items, total, pageSize: actualPageSize, loading, error, reloadData}">
				<RtypesTable
						:currentPage="page"
						:data="items"
						:loading="loading"
						:perPage="actualPageSize"
						:total="total"
						@itemActivated="handleItemActivation"
						@pageChange="page = $event"
						@sort="sorting = $event"
						backendPagination
						backendSorting
						paginated
				>
					<template #empty v-if="error">
						<div class="box has-background-warning">
							<b-button @click="reloadData" class="is-pulled-right" icon-left="reload" size="is-small" title="Перезагрузить" type="is-primary"/>
							{{error.message || error}}
						</div>
					</template>
				</RtypesTable>
			</template>
		</DataList>
	</BaseViewContainer>
</template>

<script>
	import DataList from "@components/actions/DataList";
	import BaseViewContainer from "@components/BaseViewContainer";
	import RtypesTable from "@components/rtypes/RtypesTable";

	const DEFAULT_PAGE = 1;

	export default {
		name: "RtypeListView",
		components: {RtypesTable, BaseViewContainer, DataList},
		data() {
			return {
				page: DEFAULT_PAGE,
				sorting: null,
				manualFilters: null
			};
		},
		computed: {
			manualFiltersName: {
				get() {
					return this.manualFilters?.name || "";
				},
				set(val) {
					this.setFilterValue("name", val);
				}
			},
			actualFilters() {
				const result = {};
				if (this.manualFilters) {
					Object.assign(result, this.manualFilters);
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
				this.$router.push({name: "rtypeView", params: {qPk: item.__id.toString()}});
			}
		}
	};
</script>