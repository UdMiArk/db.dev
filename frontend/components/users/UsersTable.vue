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
			<b-table-column field="created_at" label="Зарегистрирован" sortable width="100">{{row.created_at | parseDate | date}}</b-table-column>
			<b-table-column field="name" label="ФИО" sortable>{{row.name}}</b-table-column>
			<b-table-column field="email" label="E-mail" sortable>{{row.email}}</b-table-column>
			<b-table-column field="role" label="Роль">{{row.role}}</b-table-column>
			<b-table-column centered field="status" label="Отключен" sortable width="65">
				<b-icon v-bind="$options.filters.statusIconData(row.status)"/>
			</b-table-column>
		</template>
		<template #empty>
			<slot name="empty">
				<div v-if="loading">Идет загрузка..</div>
				<div v-else-if="total">На текущей странице данных нет</div>
				<div v-else>Под ваш запрос пользователей в базе не найдено. Возможно они ещё ни разу не аутентифицировались в приложении</div>
			</slot>
		</template>
	</b-table>
</template>

<script>

	import {USER_STATUSES} from "@/data/users";

	export default {
		name: "UsersTable",
		props: {
			data: {
				required: true,
				type: Array
			},
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
					statusData = USER_STATUSES.indexed[status];
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