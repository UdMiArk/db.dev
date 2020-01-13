<template>
	<DataList :filters="filters" :page="page - 1" :sorting="sorting" source="resources/list">
		<template #header>
			<div class="has-text-right">
				<h2 class="is-pulled-left is-size-3">Ресурсы ожидающие {{showOnlyMine ? "вашей " : ""}}оценки</h2>
				<b-checkbox-button style="display: inline-block" type="is-light-outlined" v-model="showOnlyMine">
					<span class="mr-sm">Только назначенные мне</span>
					<b-icon :icon="showOnlyMine ? 'checkbox-marked-outline' : 'checkbox-blank-outline'"/>
				</b-checkbox-button>
			</div>
		</template>
		<template #default="{items, total, pageSize: actualPageSize, loading, error, reloadData}">
			<ResourcesTable
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
					withProduct
			>
				<template #empty>
					<div class="box has-background-warning" v-if="error">
						<b-button @click="reloadData" class="is-pulled-right" icon-left="reload" size="is-small" title="Перезагрузить" type="is-primary"/>
						{{error.message || error}}
					</div>
					<div v-else-if="!loading">Ожидающих одобрения ресурсов не найдено</div>
				</template>
			</ResourcesTable>
		</template>
	</DataList>
</template>

<script>
	import DataList from "@components/actions/DataList";
	import ResourcesTable from "@components/resources/ResourcesTable";
	import {RESOURCE_STATUS} from "@/data/resources";
	import {mapGetters} from "vuex";

	const DEFAULT_PAGE = 1;

	export default {
		name: "AwaitingResourcesModeratorList",
		components: {ResourcesTable, DataList},
		data() {
			return {
				showOnlyMine: true,
				sorting: "created_at:desc",
				manualFilters: null,
				page: DEFAULT_PAGE
			};
		},
		computed: {
			...mapGetters("auth", ["userId"]),
			filters() {
				const result = {
					"resource.status": RESOURCE_STATUS.AWAITING,
					approved_only: 0
				};
				if (this.showOnlyMine) {
					result.status_by = this.userId;
				}
				return Object.freeze(result);
			}
		},
		methods: {
			handleItemActivation(item) {
				this.$router.push({name: "resourceView", params: {qPk: item.__id.toString()}});
			}
		}
	};
</script>
