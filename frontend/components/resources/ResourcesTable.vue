<template>
	<b-table
			:backendPagination="backendPagination"
			:backendSorting="backendSorting"
			:currentPage="currentPage"
			:data="data"
			:loading="loading"
			:paginated="paginated"
			:perPage="perPage"
			:total="total"
			@dblclick="handleItemDoubleClick"
			@page-change="handlePageChange"
			@sort="handleSort"

			aria-current-label="Текущая страница"
			aria-next-label="Следующая страница"
			aria-page-label="Страница"
			aria-previous-label="Предыдущая страница"

			hoverable
			narrowed
	>
		<template #default="{row}">
			<b-table-column field="created_at" label="Добавлен" sortable width="100">{{row.created_at | parseDate | date}}</b-table-column>
			<b-table-column field="user" label="Создатель" v-if="withUser">{{row.user.name}}</b-table-column>
			<b-table-column field="product" label="Объект продвижения" sortable>{{row.product.name}}</b-table-column>
			<b-table-column field="type" label="Тип">{{row.type.name}}</b-table-column>
			<b-table-column field="name" label="Название" sortable>{{row.name}}</b-table-column>
			<b-table-column centered field="status" label="Статус" sortable width="65">
				<b-icon v-bind="$options.filters.statusIconData(row.status)"/>
			</b-table-column>
			<b-table-column centered field="archived" label="В архиве" sortable width="85">
				<b-icon aria-label="В архиве" icon="check" title="В архиве" v-if="row.archived"/>
			</b-table-column>
		</template>
		<template #empty>
			<slot name="empty" v-if="$slots.empty"/>
			<div v-else>Под ваш запрос ресурсов в базе не найдено</div>
		</template>
	</b-table>
</template>

<script>
	import {RESOURCE_STATUSES} from "@/data/resources";

	export default {
		name: "ResourcesTable",
		props: {
			data: {
				required: true,
				type: Array
			},
			withUser: Boolean,
			backendSorting: Boolean,
			backendPagination: Boolean,
			total: Number,
			currentPage: Number,
			loading: Boolean,
			paginated: Boolean,
			perPage: Number
		},
		filters: {
			statusIconData(status) {
				const result = {},
					statusData = RESOURCE_STATUSES.indexed[status];
				if (statusData) {
					result.icon = statusData.icon;
					result["aria-label"] = result.title = statusData.label;
				}
				return result;
			}
		},
		methods: {
			/**
			 * @param {Number} page
			 */
			handlePageChange(page) {
				this.$emit("pageChange", page);
			},
			/**
			 * @param {String} field
			 * @param {String} order
			 */
			handleSort(field, order) {
				this.$emit("sort", field + ":" + order);
			},
			handleItemDoubleClick(row) {
				this.$emit("itemActivated", row);
			}
		}
	};
</script>

<style lang="scss">

</style>