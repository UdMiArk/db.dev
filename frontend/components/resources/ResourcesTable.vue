<template>
	<b-table
			:backendPagination="backendPagination"
			:backendSorting="backendSorting"
			:currentPage="currentPage"
			:data="data"
			:loading="loading"
			:paginated="paginated && (currentPage !== 1 || (total > perPage))"
			:perPage="perPage"
			:total="total"
			:row-class="() => 'is-clickable'"
			@page-change="handlePageChange"
			@sort="handleSort"

			@click="handleItemDoubleClick"

			aria-current-label="Текущая страница"
			aria-next-label="Следующая страница"
			aria-page-label="Страница"
			aria-previous-label="Предыдущая страница"

			hoverable
			narrowed
	>
		<template #default="{row}">
			<b-table-column field="created_at" label="Добавлен" sortable width="100">{{row.created_at | parseDate | date}}</b-table-column>
			<b-table-column :visible="withUser" field="user" label="Создатель">{{row.user | user}}</b-table-column>
			<b-table-column :visible="withModerator" field="status_by" label="Модератор">{{row.status_by | user}}</b-table-column>
			<b-table-column :visible="withProduct" field="product" label="Объект продвижения" sortable>{{row.product.name}}</b-table-column>
			<b-table-column field="type" label="Тип">{{row.type.name}}</b-table-column>
			<b-table-column field="name" label="Название" sortable>{{row.name}}</b-table-column>
			<b-table-column :visible="withStatus" centered field="status" label="Статус" sortable width="65">
				<b-icon v-bind="$options.filters.enumIconData(row.status, $E_STATUS)"/>
			</b-table-column>
			<b-table-column :visible="withArchived" centered field="archived" label="В архиве" sortable width="85">
				<b-icon v-bind="$options.filters.enumIconData(row.archived, $E_ARCHIVE)"/>
			</b-table-column>
		</template>
		<template #empty>
			<slot name="empty">
				<div v-if="loading">Идет загрузка..</div>
				<div v-else-if="total">На текущей странице данных нет</div>
				<div v-else>Под ваш запрос ресурсов в базе не найдено</div>
			</slot>
		</template>
	</b-table>
</template>

<script>
	import {ARCHIVE_STATUS, RESOURCE_STATUS} from "@/data/resources";

	export default {
		name: "ResourcesTable",
		props: {
			data: {
				required: true,
				type: Array
			},
			withUser: Boolean,
			withProduct: Boolean,
			withStatus: Boolean,
			withModerator: Boolean,
			withArchived: Boolean,
			backendSorting: Boolean,
			backendPagination: Boolean,
			total: Number,
			currentPage: Number,
			loading: Boolean,
			paginated: Boolean,
			perPage: Number
		},
		filters: {
			enumIconData(status, enumData) {
				const result = {},
					statusData = enumData.indexed[status];
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
		},
		created() {
			this.$E_ARCHIVE = ARCHIVE_STATUS;
			this.$E_STATUS = RESOURCE_STATUS;
		}
	};
</script>

<style lang="scss">

</style>