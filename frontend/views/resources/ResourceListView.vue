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
					<b-menu-item :active="!activeMenuItem" @click="setActiveMenuItem(null)" key="all" label="Все"/>
					<b-menu-item :active="market === activeMenuItem" :expanded="isMarketExpanded(market)" :key="market.__id" :label="market.name" @click="setActiveMenuItem(market)" v-for="market in structure">
						<span v-if="!market.products">Ресурсов для выбранного рынка не найдено</span>
						<b-menu-item :active="product === activeMenuItem" :key="product.__id" :label="product.name" @click="setActiveMenuItem(product)" v-else v-for="product in market.products"/>
					</b-menu-item>
				</b-menu-list>
			</b-menu>
		</template>
		<DataList :filters="actualFilters" :page="page - 1" :preventUpdate="structureLoading" :sorting="sorting" source="resources/list">
			<template #header>
				<div>
					<b-button :to="{name: 'resourceCreate'}" class="is-pulled-right" tag="router-link">Добавить ресурс</b-button>
					<input class="input is-inline mr-sm" placeholder="Тип" type="text" v-model.lazy="manualFiltersType"/>
					<input class="input is-inline mr-sm" placeholder="Создатель" type="text" v-if="!ownResourcesOnly" v-model.lazy="manualFiltersUser"/>
					<input class="input is-inline mr-sm" placeholder="Объект Продвижения" type="text" v-if="!menuProductSelected" v-model.lazy="manualFiltersProduct"/>
					<b-button @click="manualFilters = null" icon-left="close" title="Очистить фильтры" v-if="manualFilters"/>
				</div>
			</template>
			<template #default="{items, total, pageSize: actualPageSize, loading, error, reloadData}">
				<ResourcesTable
						:currentPage="page"
						:data="items"
						:loading="loading || structureLoading"
						:perPage="actualPageSize"
						:total="total"
						:withUser="!ownResourcesOnly"
						:withProduct="!menuProductSelected"
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
				structureLoading: null,
				expanded: {},
				page: DEFAULT_PAGE,
				sorting: null,
				manualFilters: null,
				market: null,
				product: null,
				activeMenuItem: null,
				activeMenuItemId: null,
				ownResourcesOnly: false
			};
		},
		computed: {
			menuProductSelected() {
				return this.activeMenuItem?.id[0] === "p";
			},
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
				if (this.activeMenuItem) {
					const item = this.activeMenuItem;
					result[item.market_id ? "product_id" : "market_id"] = item.__id;
				}
				if (this.ownResourcesOnly) {
					result.own_only = 1;
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
			reloadStructure() {
				this.structure = null;
				this.structureLoading = true;
				this.$apiGetJ("resources/structure", {"only_mine": this.ownResourcesOnly ? 1 : undefined}).then(({data}) => {
					this.structure = deepFreeze(data);
					const lastActiveId = this._lastSetActiveMenuItemId;
					let newActiveItem = null;
					if (lastActiveId) {
						for (const market of this.structure) {
							if (market.id === lastActiveId) {
								newActiveItem = market;
								break;
							} else if (market.products) {
								for (const product of market.products) {
									if (product.id === lastActiveId) {
										newActiveItem = product;
										break;
									}
								}
							}
						}
					}
					this.setActiveMenuItem(newActiveItem);
				}).catch(err => {
					this.structure = Object.freeze([]);
					this.$handleErrorWithBuefy(err);
				}).finally(() => this.structureLoading = false);
			},
			isMarketExpanded(market) {
				const activeMenuItem = this.activeMenuItem;
				return activeMenuItem && (activeMenuItem === market || activeMenuItem.market_id === market.__id);
			},
			handleItemActivation(item) {
				this.$router.push({name: "resourceView", params: {qPk: item.__id.toString()}});
			},
			setActiveMenuItem(item) {
				this.activeMenuItem = item;
				this._lastSetActiveMenuItemId = item ? item.id : null;
			}
		},
		watch: {
			ownResourcesOnly(val) {
				this.activeMenuItem = null;
				this.reloadStructure();
				if (val && this.manualFilters?.user) {
					this.setFilterValue("user", "");
				}
			},
			activeMenuItem(newActiveValue) {
				this.page = DEFAULT_PAGE;
				if (this.manualFilters?.product && this.menuProductSelected) {
					this.setFilterValue("prod", "");
				}
			}
		},
		created() {
			this._lastSetActiveMenuItemId = null;
			this.reloadStructure();
		}
	};
</script>

<style lang="scss">

</style>