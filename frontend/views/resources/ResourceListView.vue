<template>
	<ViewWithRightPanelContainer>
		<template #sidebar>
			<div class="box is-narrow">
				<b-checkbox-button type="is-white-outline" v-model="ownResourcesOnly">
					<span class="mr-sm">Только мои</span>
					<b-icon :icon="ownResourcesOnly ? 'checkbox-marked-outline' : 'checkbox-blank-outline'"/>
				</b-checkbox-button>
			</div>
			<b-menu :accordion="false">
				<b-menu-list label="Рынки">
					<b-menu-item :active="!activeMenuItem" @click="activeMenuItem = null" key="all" label="Все"/>
					<b-menu-item :active="market === activeMenuItem" :expanded="isMarketExpanded(market)" :key="market.__id" :label="market.name" @click="activeMenuItem = market" v-for="market in structure">
						<span v-if="!market.products">Ресурсов для выбранного рынка не найдено</span>
						<b-menu-item :active="product === activeMenuItem" :key="product.__id" :label="product.name" @click="activeMenuItem = product" v-else v-for="product in market.products"/>
					</b-menu-item>
				</b-menu-list>
			</b-menu>
		</template>
		<DataList :filters="actualFilters" :page="page - 1" :sorting="sorting" source="resources/list">
			<template #header>
				<div class="columns">
					<div class="column"></div>
					<div class="column is-one-quarter-desktop has-text-right">
						<b-button :to="{name: 'resourceCreate'}" tag="router-link">Добавить ресурс</b-button>
					</div>
				</div>
			</template>
			<template #default="{items, total, pageSize: actualPageSize, loading, error, reloadData}">
				<ResourcesTable
						:currentPage="page"
						:data="items"
						:loading="loading"
						:perPage="actualPageSize"
						:total="total"
						:withUser="!ownResourcesOnly"
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
	</ViewWithRightPanelContainer>
</template>

<script>
	import ViewWithRightPanelContainer from "@components/ViewWithRightPanelContainer";
	import {deepFreeze} from "@/plugins/object";
	import ResourcesTable from "@components/resources/ResourcesTable";
	import DataList from "@components/actions/DataList";
	import Layout from "@components/app/Layout";

	const DEFAULT_PAGE = 1;

	export default {
		name: "ResourceListView",
		components: {Layout, DataList, ResourcesTable, ViewWithRightPanelContainer},
		data() {
			return {
				structure: null,
				expanded: {},
				page: DEFAULT_PAGE,
				sorting: null,
				manualFilters: null,
				market: null,
				product: null,
				activeMenuItem: null,
				ownResourcesOnly: false
			};
		},
		computed: {
			actualFilters() {
				const result = {};
				if (this.manualFilters) {
					Object.apply(result, this.manualFilters);
				}
				if (this.activeMenuItem) {
					const item = this.activeMenuItem;
					result[item.market_id ? "product" : "market"] = item.__id;
				}
				if (this.ownResourcesOnly) {
					result.own_only = 1;
				}
				return Object.keys(result).length ? Object.freeze(result) : null;
			}
		},
		methods: {
			reloadStructure() {
				this.structure = null;
				this.$apiGetJ("resources/structure").then(({data}) => {
					this.structure = deepFreeze(data);
				}).catch(err => {
					this.structure = Object.freeze([]);
					this.$handleErrorWithBuefy(err);
				});
			},
			isMarketExpanded(market) {
				const activeMenuItem = this.activeMenuItem;
				return activeMenuItem && (activeMenuItem === market || activeMenuItem.market_id === market.__id);
			},
			handleItemActivation(item) {
				this.$router.push({name: "resourceView", params: {qPk: item.__id.toString()}});
			}
		},
		watch: {
			ownResourcesOnly() {
				this.activeMenuItem = null;
				this.reloadStructure();
			},
			activeMenuItem() {
				this.page = DEFAULT_PAGE;
			}
		},
		created() {
			this.reloadStructure();
		}
	};
</script>

<style lang="scss">

</style>