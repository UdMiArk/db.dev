<template>
	<BaseForm #default="{props, changes}" :data="item" :locked="processing" @submit="handleSubmit" class="app-form card" ref="display">
		<header class="card-header">
			<slot name="header">
				<span class="card-header-title">Редактирование типа ресурса</span>
			</slot>
		</header>
		<section class="card-content">
			<b-field
					:message="errors && errors.name"
					:type="errors && errors.name && 'is-danger'"
					label="Название типа"
			>
				<b-input :disabled="processing" :readonly="readonly" name="name" required v-model="props.name"/>
			</b-field>
			<b-field
					:message="errors && errors.responsible_id"
					:type="errors && errors.responsible_id && 'is-danger'"
					label="Ответственный"
			>
				<UserInput
						:disabled="processing"
						:readonly="readonly"
						:source="availableUsers"
						:value="getResponsibleUser(props.responsible_id)"
						@input="props.responsible_id = $event ? $event.__id : null"
				/>
			</b-field>
			<b-field
					:message="errors && errors.description"
					:type="errors && errors.description && 'is-danger'"
					label="Описание"
			>
				<b-input :disabled="processing" :readonly="readonly" name="description" type="textarea" v-model="props.description"/>
			</b-field>
			<RtypeAttributesEditBlock
					:changed="changes && changes.typeAttributes"
					:disabled="processing"
					:errors="errors && errors.typeAttributes"
					:readonly="readonly || readonlyFields"
					:value="item.typeAttributes"
					@input="handleInput(props, 'typeAttributes', $event)"
			/>
		</section>
		<footer class="card-content has-background-light" v-if="$slots.controls || !readonly">
			<slot name="controls">
				<div class="has-text-right">
					<b-button :loading="processing" native-type="submit" type="is-primary" v-if="!readonly">Отправить</b-button>
				</div>
			</slot>
		</footer>
	</BaseForm>
</template>

<script>
	import BaseForm from "@components/form/BaseForm";
	import RtypeAttributesEditBlock from "@components/rtypes/RtypeAttributesEditBlock";
	import {deepFreeze} from "@plugins/object";
	import UserInput from "@components/users/UserInput";

	export default {
		name: "RtypeFormBlock",
		components: {UserInput, RtypeAttributesEditBlock, BaseForm},
		props: {
			item: Object,
			readonly: Boolean,
			readonlyFields: Boolean,
			errors: Object,
			processing: Boolean
		},
		data() {
			return {
				availableUsers: null
			};
		},
		methods: {
			handleSubmit($event) {
				this.$emit("submit", $event);
			},
			handleInput(props, prop, value) {
				props[prop] = value;
			},
			reset() {
				this.$refs.display?.reset();
			},
			reloadAvailableUsers() {
				this.availableUsers = null;
				this.$apiGetJ("resource-types/responsible")
					.then(({data}) => this.availableUsers = deepFreeze(data))
					.catch(this.$handleErrorWithBuefy);
			},
			getResponsibleUser(userId) {
				if (!userId) {
					return null;
				}
				if (this.item.responsible?.__id === userId) {
					return this.item.responsible;
				}
				if (this.availableUsers) {
					return this.availableUsers.find(x => x.__id === userId);
				} else {
					return null;
				}
			}
		},
		created() {
			this.reloadAvailableUsers();
		}
	};
</script>
