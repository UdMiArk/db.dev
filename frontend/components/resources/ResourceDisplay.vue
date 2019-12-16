<template>
	<div class="card">
		<header class="card-header">
			<slot name="header"><span class="card-header-title">Ресурс "{{data.name}}"</span></slot>
		</header>
		<section class="card-content">
			<slot name="default-top"/>
			<div class="columns">
				<div class="column is-half is-full-mobile">
					<b-field label="Создатель">
						<b-input :value="data.user.name" readonly/>
					</b-field>
				</div>
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Дата создания">
						<b-input :value="data.created_at | parseDate | date" readonly/>
					</b-field>
				</div>
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Статус">
						<b-input :value="data.status | enumLabel($STATUSES)" readonly/>
					</b-field>
				</div>
			</div>
			<div class="columns">
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Рынок">
						<b-input :value="data.product.market.name" readonly/>
					</b-field>
				</div>
				<div class="column is-three-quarters-desktop is-half">
					<b-field label="Объект продвижения">
						<b-input :value="data.product.name" readonly/>
					</b-field>
				</div>
			</div>
			<div class="columns">
				<div class="column is-one-quarter-desktop is-half">
					<b-field label="Тип ресурса">
						<b-input :value="data.type.name" readonly/>
					</b-field>
				</div>
				<div class="column is-three-quarters-desktop is-half">
					<b-field label="Имя ресурса">
						<b-input :value="data.name" readonly/>
					</b-field>
				</div>
			</div>
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
	import {RESOURCE_STATUSES} from "@/data/resources";

	export default {
		name: "ResourceDisplay",
		components: {ResourceDataDisplayBlock},
		props: {
			data: {
				type: Object,
				required: true
			}
		},
		created() {
			this.$STATUSES = RESOURCE_STATUSES;
		}
	};
</script>