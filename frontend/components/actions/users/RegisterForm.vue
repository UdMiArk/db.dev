<template>
	<BaseForm #default="{props}" :data="defaultValues" :locked="processing" @submit="handleSubmit" class="app-form-login box">
		<b-field
				:message="errors && errors.domain"
				:type="errors && errors.domain && 'is-danger'"
				label="Домен"
		>
			<b-select :disabled="processing" expanded name="domain" required v-model="props.domain">
				<option :key="domain.value"
						:value="domain.value"
						v-for="domain in availableDomains"
				>{{domain.label}}
				</option>
			</b-select>
		</b-field>
		<b-field
				:message="errors && errors.login"
				:type="errors && errors.login && 'is-danger'"
				label="Логин"
		>
			<b-input :disabled="processing" name="login" required v-model="props.login"/>
		</b-field>
		<b-button :loading="processing" expanded native-type="submit" type="is-primary">Зарегестрировать</b-button>
	</BaseForm>
</template>

<script>
	import {mapState} from "vuex";
	import BaseForm from "@components/form/BaseForm";

	export default {
		name: "UserRegisterForm",
		components: {BaseForm},
		data() {
			return {
				processing: false,
				errors: undefined
			};
		},
		computed: {
			...mapState("auth", {availableDomains: "domains"}),
			defaultValues() {
				return Object.freeze({
					domain: this.availableDomains && this.availableDomains.length && this.availableDomains[0].value,
					login: ""
				});
			}
		},
		methods: {
			handleSubmit({changes}) {
				this.processing = true;
				this.errors = undefined;
				this.$apiPostJ("users/register", changes)
					.then(({data}) => {
						if (data.success) {
							this.$emit("success", data);
						} else {
							this.errors = Object.freeze(data.errors);
						}
					})
					.catch(this.$handleErrorWithBuefy)
					.finally(() => this.processing = false);
			}
		}
	};
</script>
