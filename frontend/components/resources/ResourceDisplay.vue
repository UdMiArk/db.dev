<template>
	<div class="card">
		<!-- <header class="card-header">
			<slot name="header">
				<span class="card-header-title is-size-4">
					Ресурс "{{data.name}}"
				</span>
				<span class="card-header-icon">
					<b-tag
							:type="awaitingArchivation ? 'is-warning' : 'is-light'"
							class="is-pulled-right"
							size="is-medium" v-if="archivedData"
					><b-icon :icon="archivedData.icon" class="mr-xs is-size-7"/><span style="vertical-align: top">{{archivedData.label}}</span></b-tag>
				</span>
			</slot>
		</header> -->
		<section class="card-content">
			<slot name="default-top"/>
			<div class="columns">
				<div class="column is-three-quarters-desktop is-half">
					<b-field label="Объект продвижения">
						<b-input :value="data.product.name" readonly/>
					</b-field>
				</div>
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Рынок">
						<b-input :value="data.product.market.name" readonly/>
					</b-field>
				</div>
			</div>
			<div class="columns">
				<div class="column is-one-quarter-desktop is-half-tablet">
					<b-field label="Тип ресурса">
						<b-input :value="data.type.name" readonly/>
					</b-field>
				</div>
				<div class="column">
					<b-field label="Имя ресурса">
						<b-input :value="data.name" readonly/>
					</b-field>
				</div>
			</div>
			<div class="columns is-multiline">
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Статус">
						<b-input :title="data | statusTitle" :value="data.status | enumLabel($STATUSES)" readonly/>
					</b-field>
				</div>
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Дата создания">
						<b-input :value="data.created_at | parseDate | date" readonly/>
					</b-field>
				</div>
				<div class="column">
					<b-field label="Создатель">
						<b-input :value="data.user.name" readonly/>
					</b-field>
				</div>
			</div>
			<template v-if="archivedData">
				<div class="box has-background-warning" v-if="awaitingArchivation">
					В данный момент ресурс находится в просессе архивации/деархивации и его хранилище не доступно
					<div class="has-text-centered pt-md" v-if="data.archived_queue">
						<b-button @click="$emit('startWaiting')">Перезагрузить страницу после готовности</b-button>
					</div>
				</div>
				<div class="box has-background-light" v-else>
					Ресурс находится в архиве.
					Вы можете скачать все его данные одним файлом, скачивание отдельных файлов отключено.
					<div class="has-text-centered pt-md">
						<b-button :href="archiveLink" download icon-left="package-down" tag="a">Скачать архив</b-button>
					</div>
				</div>
			</template>
			<ResourceDataDisplayBlock :resource="data" :resourceType="data.type" :value="data.data"/>
			<b-field label="Комментарий" v-if="data.comment">
				<b-input name="comment" readonly type="textarea" v-model="data.comment"/>
			</b-field>
			<slot name="default"/>
		</section>
		<footer class="card-content has-background-light" v-if="$slots.footer">
			<slot name="footer"/>
		</footer>
	</div>
</template>

<script>
	import ResourceDataDisplayBlock from "@components/resources/ResourceDataDisplayBlock";
	import {ARCHIVE_STATUS, RESOURCE_STATUS} from "@/data/resources";
	import {getApiResourceArchiveLink} from "@plugins/api";
	import {formatDate, parseServerDate} from "@plugins/datetime";

	export default {
		name: "ResourceDisplay",
		components: {ResourceDataDisplayBlock},
		props: {
			data: {
				type: Object,
				required: true
			}
		},
		computed: {
			archivedData() {
				const status = this.data.archived;
				if (status === ARCHIVE_STATUS.NOT_ARCHIVED) {
					return null;
				}
				return ARCHIVE_STATUS.indexed[status];
			},
			awaitingArchivation() {
				const status = this.data.archived;
				return (
					status === ARCHIVE_STATUS.AWAITING_ARCHIVATION
					|| status === ARCHIVE_STATUS.AWAITING_DEARCHIVATION
				);
			},
			archiveLink() {
				if (this.data.archived === ARCHIVE_STATUS.ARCHIVED) {
					return getApiResourceArchiveLink(this.data.__id);
				}
				return undefined;
			}
		},
		filters: {
			statusTitle(data) {
				if (data.status) {
					return formatDate(parseServerDate(data.status_at)) + ", модератор: " + data.statusBy.name;
				} else if (data.statusBy) {
					return "Назначен модератор: " + data.statusBy.name;
				}
			}
		},
		created() {
			this.$STATUSES = RESOURCE_STATUS;
			this.$E_ARCHIVE = ARCHIVE_STATUS;
		}
	};
</script>