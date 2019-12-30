<template>
	<b-table
			:backendPagination="backendPagination"
			:backendSorting="backendSorting"
			:currentPage="currentPage"
			:data="data"
			:loading="loading"
			:paginated="paginated"
			:perPage="perPage"
			:row-class="(row) => 'is-clickable'+ (row.disabled ? ' is-light' : '')"
			:total="total"
			@click="handleItemDoubleClick"
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
			<b-table-column field="name" label="Название" sortable>{{row.name}}</b-table-column>
			<b-table-column field="responsible" label="Ответственный" sortable>{{row.responsible | user}}</b-table-column>
			<b-table-column field="description" label="Описание">
				<span :title="row.description" class="has-ellipsis">{{row.description.trim().split("\n")[0]}}</span>
			</b-table-column>
		</template>
		<template #empty>
			<slot name="empty">
				<div v-if="loading">Идет загрузка..</div>
				<div v-else-if="total">На текущей странице данных нет</div>
				<div v-else>Под ваш запрос типов ресурсов в базе не найдено</div>
			</slot>
		</template>
	</b-table>
</template>

<script>
	export default {
		name: "RtypesTable",
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