<template>
	<BaseForm #default="{props}" :data="defaultValues" :locked="processing" @submit="handleSubmit" class="app-form-login box">
		<div class="box">
			<p>
				Нам не удалось автоматически соединить вашу учетную запись с записью в
				<a href="https://erp.db.dev" target="_blank">ERP.DB.DEV</a>.
				Эта связь необходима для получения списка объектов продвижения доступных для управления вами.
			</p>
			<p>Для продолжения пожалуйста введите данные используемые вами для входа в SC.</p>
		</div>
		<b-field
				:message="errors && errors.email"
				:type="errors && errors.email && 'is-danger'"
				label="E-mail"
		>
			<b-input :disabled="processing" name="email" required v-model="props.email"/>
		</b-field>
		<b-field
				:message="errors && errors.password"
				:type="errors && errors.password && 'is-danger'"
				label="Пароль"
		>
			<b-input :disabled="processing" name="password" password-reveal required type="password" v-model="props.password"/>
		</b-field>
		<b-button :loading="processing" expanded native-type="submit" type="is-primary">Войти</b-button>
		<a class="app-form-login__secondary-link" href="https://erp.db.dev/register" target="_blank">Зарегестрироваться в ERP-системе</a>
	</BaseForm>
</template>

<script>
	import {mapMutations} from "vuex";
	import BaseForm from "@components/form/BaseForm";
	import {deepFreeze} from "@plugins/object";

	export default {
		name: "ScAuthForm",
		components: {BaseForm},
		data() {
			return {
				processing: false,
				errors: undefined
			};
		},
		computed: {
			defaultValues() {
				return Object.freeze({
					email: "",
					password: ""
				});
			}
		},
		methods: {
			...mapMutations("auth", ["setAuthData"]),
			handleSubmit({changes: {domain, login, password, remember}}) {
				this.processing = true;
				this.errors = undefined;
				this.doLogin(domain, login, password, remember)
					.then(({data}) => {
						if (data.success) {
							this.$emit("success", deepFreeze(data.data));
						} else {
							this.errors = Object.freeze(data.errors);
						}
					})
					.catch(this.$handleErrorWithBuefy)
					.finally(() => this.processing = false);
			},
			doLogin(email, password) {
				return this.$apiPostJ("resources/sc-login", {email, password});
			}
		}
	};
</script>
