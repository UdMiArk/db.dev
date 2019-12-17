<template>
	<BaseViewContainer>
		<div class="box is-warning" v-if="error">{{error.message || error}}</div>
		<div class="card" ref="display">
			<header class="card-header">
				<h2 class="card-header-title">Пользователь
					<template v-if="item"> "{{data.login}}"</template>
				</h2>
			</header>
			<section class="card-content">
				<b-field label="ФИО">
					<b-input :value="data.name" readonly/>
				</b-field>
				<div class="columns is-multiline">
					<div class="column is-one-fifth-fullhd is-one-quarter-desktop is-half">
						<b-field label="Статус">
							<b-input :value="data.status | enumLabel($STATUS)" readonly/>
						</b-field>
					</div>
					<div class="column is-one-fifth-fullhd is-one-quarter-desktop is-half">
						<b-field label="Дата регистрации">
							<b-input :value="data.created_at | parseDate | date" readonly/>
						</b-field>
					</div>
					<div class="column is-one-fifth-fullhd is-half">
						<b-field label="E-mail">
							<b-input :value="data.email" readonly/>
						</b-field>
					</div>
					<div class="column is-one-fifth-fullhd is-half">
						<b-field label="Логин">
							<b-input :value="data.login" readonly/>
						</b-field>
					</div>
					<div class="column is-one-fifth-fullhd is-half">
						<b-field label="Домен">
							<b-input :value="data.domain" readonly/>
						</b-field>
					</div>
				</div>
			</section>
			<section class="card-content">
				<h3 class="is-size-3">Роли</h3>
				<Loader v-if="!roles"/>
				<RoleToggler :roles="roles" :value="data.roles" @toggle="handleRoleToggle" v-else/>
			</section>
		</div>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import {deepFreeze} from "@/plugins/object";
	import {USER_STATUSES} from "@/data/users";
	import {ensureRegistered as ensureUsersModuleRegistered} from "@/store/module-users";
	import Loader from "@components/Loader";
	import RoleToggler from "@components/users/RoleToggler";
	import {mapState} from "vuex";

	export default {
		name: "UserView",
		components: {RoleToggler, Loader, BaseViewContainer},
		props: {
			qPk: String
		},
		data() {
			return {
				item: null,
				error: null
			};
		},
		computed: {
			...mapState("users", ["roles"]),
			data() {
				return this.item || Object.freeze({});
			},
			canDoActions() {
				return this.canApprove || this.canSendToArchive || this.canReturnFromArchive;
			},
			canSendToArchive() {
				return this.item?.canArchive && !this.item.archived;
			},
			canReturnFromArchive() {
				return this.item?.canArchive && this.item.archived;
			},
			canApprove() {
				return this.item?.canApprove;
			}
		},
		methods: {
			showLoading() {
				return this.$buefy.loading.open({container: this.$refs.display || this.$el});
			},

			reloadItem() {
				this.error = null;
				this.item = null;
				const processing = this.showLoading();
				return (
					this.$apiGetJ("users/view/" + this.qPk)
						.then(({data}) => this.item = deepFreeze(data))
						.catch(err => {
							this.error = err;
							this.$handleErrorWithBuefy(err);
						}).finally(() => processing.close())
				);
			},

			handleRoleToggle({role, state}) {
				const processing = this.showLoading();
				const oldItem = this.item,
					changedItem = Object.assign({}, oldItem);
				changedItem.roles = changedItem.roles.slice();
				const roleIdx = changedItem.roles.indexOf(role);
				if (state) {
					if (roleIdx === -1) {
						changedItem.roles.push(role);
					}
				} else {
					if (roleIdx !== -1) {
						changedItem.roles.splice(roleIdx, 1);
					}
				}
				Object.freeze(changedItem.roles);
				this.item = Object.freeze(changedItem);
				return (
					this.$apiPostJ("users/toggle-role/" + this.qPk, {role, state})
						.then(({data}) => {
							if (data.success) {
								const newItem = Object.assign({}, oldItem);
								newItem.roles = Object.freeze(data.roles);
								this.item = Object.freeze(newItem);
							} else {
								throw new Error(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch((err) => {
							this.item = oldItem;
							this.$handleErrorWithBuefy(err);
						})
						.finally(() => processing.close())
				);
			}
		},
		beforeCreate() {
			if (!this.$store.state.users) {
				ensureUsersModuleRegistered(this.$store);
			}
		},
		created() {
			this.$STATUS = USER_STATUSES;
		},
		mounted() {
			this.reloadItem();
		}
	};
</script>

<style lang="scss">

</style>