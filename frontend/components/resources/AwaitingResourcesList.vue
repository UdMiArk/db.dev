<template>
	<DataList :filters="filters" :page="page - 1" :sorting="sorting" source="resources/list">
		<template #header>
			<div class="has-text-right">
				<h2 class="is-pulled-left is-size-3">Ваши ресурсы ожидающие утверждения</h2>
				<b-button :to="{name: 'resourceCreate'}" class="is-primary" outlined tag="router-link">Добавить ресурс</b-button>
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
					<div v-else-if="!loading">Созданных вами ресурсов ожидающих одобрения не найдено</div>
				</template>
			</ResourcesTable>
		</template>
	</DataList>
</template>

<script>
	import DataList from "@components/actions/DataList";
	import ResourcesTable from "@components/resources/ResourcesTable";

	import {RESOURCE_STATUS} from "@/data/resources";

	const DEFAULT_PAGE = 1;

	export default {
		name: "AwaitingResourcesList",
		components: {ResourcesTable, DataList},
		data() {
			return {
				sorting: null,
				manualFilters: null,
				page: DEFAULT_PAGE
			};
		},
		computed: {
			filters() {
				return Object.freeze({
					own_only: 1,
					approved_only: 0,
					"resource.status": RESOURCE_STATUS.AWAITING
				});
			}
		},
		methods: {
			handleItemActivation(item) {
				this.$router.push({name: "resourceView", params: {qPk: item.__id.toString()}});
			}
		}
	};
</script>
