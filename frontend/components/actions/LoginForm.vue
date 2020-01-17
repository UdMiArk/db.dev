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
		<b-field
				:message="errors && errors.password"
				:type="errors && errors.password && 'is-danger'"
				label="Пароль"
		>
			<b-input :disabled="processing" name="password" password-reveal required type="password" v-model="props.password"/>
		</b-field>
		<b-field>
			<b-checkbox :disabled="processing" name="remember" v-model="props.remember">Запомнить меня</b-checkbox>
		</b-field>
		<b-button :loading="processing" expanded native-type="submit" type="is-primary">Войти</b-button>
	</BaseForm>
</template>

<script>
	import {mapMutations, mapState} from "vuex";
	import BaseForm from "@components/form/BaseForm";

	export default {
		name: "LoginForm",
		components: {BaseForm},
		props: {
			back: String
		},
		data() {
			return {
				processing: false,
				errors: undefined
			};
		},
		computed: {
			...mapState("auth", {availableDomains: "domains", "isSimpleUser": "simpleUser"}),
			defaultValues() {
				return Object.freeze({
					domain: this.availableDomains && this.availableDomains.length && this.availableDomains[0].value,
					login: "",
					password: "",
					remember: true
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
							this.setAuthData(data.auth);
							this.$router.replace(this.back || {name: this.isSimpleUser ? "resourcesList" : "home"});
						} else {
							this.errors = Object.freeze(data.errors);
						}
					})
					.catch(this.$handleErrorWithBuefy)
					.finally(() => this.processing = false);
			},
			doLogin(domain, login, password, remember = true) {
				return this.$apiPostJ("auth/login", {domain, login, password, remember});
			}
		}
	};
</script>
