<template>
	<BaseViewContainer>
		<DataList :filters="actualFilters" :page="page - 1" :sorting="sorting" source="users/list">
			<template #header>
				<div>
					<b-button :to="{name: 'userRegister'}" class="is-pulled-right" tag="router-link">Добавить пользователя</b-button>
					<input class="input is-inline mr-sm" placeholder="Имя" type="text" v-model.lazy="manualFiltersName"/>
					<input class="input is-inline mr-sm" placeholder="E-mail" type="text" v-model.lazy="manualFiltersEmail"/>
					<b-button @click="manualFilters = null" icon-left="close" title="Очистить фильтры" v-if="manualFilters"/>
				</div>
			</template>
			<template #default="{items, total, pageSize: actualPageSize, loading, error, reloadData}">
				<UsersTable
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
					<template #empty>
						<div class="box has-background-warning" v-if="error">
							<b-button @click="reloadData" class="is-pulled-right" icon-left="reload" size="is-small" title="Перезагрузить" type="is-primary"/>
							{{error.message || error}}
						</div>
					</template>
				</UsersTable>
			</template>
		</DataList>
	</BaseViewContainer>
</template>

<script>
	import {deepFreeze} from "@/plugins/object";
	import ResourcesTable from "@components/resources/ResourcesTable";
	import DataList from "@components/actions/DataList";
	import Layout from "@components/app/Layout";
	import BaseViewContainer from "@components/BaseViewContainer";
	import UsersTable from "@components/users/UsersTable";

	const DEFAULT_PAGE = 1;

	export default {
		name: "UserListView",
		components: {UsersTable, BaseViewContainer, Layout, DataList, ResourcesTable},
		data() {
			return {
				structure: null,
				expanded: {},
				page: DEFAULT_PAGE,
				sorting: null,
				manualFilters: null,
				market: null,
				product: null,
				ownResourcesOnly: false
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
			manualFiltersEmail: {
				get() {
					return this.manualFilters?.email || "";
				},
				set(val) {
					this.setFilterValue("email", val);
				}
			},
			actualFilters() {
				const result = {};
				if (this.manualFilters) {
					Object.assign(result, this.manualFilters);
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
				this.$apiGetJ("resources/structure").then(({data}) => {
					this.structure = deepFreeze(data);
				}).catch(err => {
					this.structure = Object.freeze([]);
					this.$handleErrorWithBuefy(err);
				});
			},
			handleItemActivation(item) {
				this.$router.push({name: "userView", params: {qPk: item.__id.toString()}});
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