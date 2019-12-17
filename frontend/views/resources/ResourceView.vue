<template>
	<BaseViewContainer>
		<div class="box is-warning" v-if="error">{{error.message || error}}</div>
		<Loader v-else-if="item === null"/>
		<ResourceDisplay :data="item" ref="display" v-else>
			<template #footer v-if="canDoActions">
				<div class="has-text-right">
					<b-button @click="sendToArchive" v-if="canSendToArchive">Отправить в архив</b-button>
					<b-button @click="returnFromArchive" v-if="canReturnFromArchive">Вернуть из архива</b-button>
					<template v-if="canApprove">
						<b-button @click="setResourceStatus(false)" type="is-warning">Отклонить</b-button>
						<b-button @click="setResourceStatus(true)" type="is-primary ml-sm">Утвердить</b-button>
					</template>
				</div>
			</template>
		</ResourceDisplay>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import Loader from "@components/Loader";
	import {deepFreeze} from "@/plugins/object";
	import ResourceDisplay from "@components/resources/ResourceDisplay";
	import {mapState} from "vuex";

	export default {
		name: "ResourceView",
		components: {ResourceDisplay, Loader, BaseViewContainer},
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
			...mapState("auth", ["permissions"]),
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
			reloadItem() {
				this.error = null;
				this.item = null;
				return (
					this.$apiGetJ("resources/view/" + this.qPk)
						.then(({data}) => this.item = deepFreeze(data))
						.catch(err => {
							this.error = err;
							this.$handleErrorWithBuefy(err);
						})
				);
			},

			setResourceStatus(approved) {
				const processing = this.$buefy.loading.open({container: this.$refs.display.$el});
				return (
					this.$apiPostJ("resources/set-status/" + this.qPk, {approved: approved})
						.then(({data}) => {
							if (data.success) {
								this.item = deepFreeze(data.resource);
							} else {
								throw new Error(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch(this.$handleErrorWithBuefy)
						.finally(() => processing.close())
				);
			},
			sendToArchive() {
				const processing = this.$buefy.loading.open({container: this.$refs.display.$el});
				return (
					this.$apiPostJ("resources/archive/" + this.qPk)
						.then(({data}) => {
							if (data.success) {
								this.item = deepFreeze(data.resource);
							} else {
								throw new Error(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch(this.$handleErrorWithBuefy)
						.finally(() => processing.close())
				);
			},
			returnFromArchive() {
				const processing = this.$buefy.loading.open({container: this.$refs.display.$el});
				return (
					this.$apiPostJ("resources/dearchive/" + this.qPk)
						.then(({data}) => {
							if (data.success) {
								this.item = deepFreeze(data.resource);
							} else {
								throw new Error(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch(this.$handleErrorWithBuefy)
						.finally(() => processing.close())
				);
			}
		},
		created() {
			this.reloadItem();
		}

	};
</script>

<style lang="scss">

</style>