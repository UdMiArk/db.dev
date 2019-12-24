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
		<ResourceList :additionalDataLoading="structureLoading" :additionalFilters="additionalFilters" :showProduct="!menuProductSelected" :showUser="!ownResourcesOnly" defaultActivation showStatus/>
	</ViewWithRightPanelContainer>
</template>

<script>
	import ViewWithRightPanelContainer from "@components/ViewWithRightPanelContainer";
	import {deepFreeze} from "@/plugins/object";
	import ResourceList from "@components/actions/resources/ResourceList";

	const DEFAULT_PAGE = 1;

	export default {
		name: "ResourceListView",
		components: {ResourceList, ViewWithRightPanelContainer},
		data() {
			return {
				structure: null,
				structureLoading: null,
				expanded: {},
				page: DEFAULT_PAGE,
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
			additionalFilters() {
				const result = {};
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
			},
			activeMenuItem(newActiveValue) {
				this.page = DEFAULT_PAGE;
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