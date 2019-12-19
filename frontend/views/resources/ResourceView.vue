<template>
	<BaseViewContainer>
		<div class="box is-warning" v-if="error">{{error.message || error}}</div>
		<Loader v-else-if="item === null"/>
		<ResourceDisplay :data="item" @startWaiting="showArchivationProcess(item.archived_queue)" ref="display" v-else>
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
		<b-loading :active.sync="archivationInProcess" can-cancel>
			<div class="box has-text-centered" style="z-index: 1">
				<b-button @click="archivationInProcess = false" class="is-pulled-right" icon-left="close"/>
				<p>
					Был запущен процесс архивации/распаковки ресурса.<br/>
					Этот процесс может занять продолжительное время (зависит от размера файлов и загруженности сервера).<br/>
					В течении этого периода файлы ресурса недоступны для скачивания.<br/>
					<b>Вы можете покинуть страницу или закрыть отображение процесса</b> щелкнув по нему, это не затронет процесс на сервере.<br/>
					Или вы можете дождаться автоматической перезагрузки данных после того как сервер отрапартует о завершении.<br/>
				</p>
				<div class="loading-icon" style="height: 120px"/>
			</div>
		</b-loading>
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
				error: null,
				archivationInProcess: false
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
								this.showArchivationProcess(data.queue_id);
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
					this.$apiPostJ("resources/unpack/" + this.qPk)
						.then(({data}) => {
							if (data.success) {
								this.item = deepFreeze(data.resource);
								this.showArchivationProcess(data.queue_id);
							} else {
								throw new Error(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch(this.$handleErrorWithBuefy)
						.finally(() => processing.close())
				);
			},
			showArchivationProcess(queueId) {
				this.archivationInProcess = true;
				let runningRequest;
				const keyInterval = setInterval(() => {
					if (!runningRequest) {
						if (!this.archivationInProcess) {
							clearInterval(keyInterval);
						}
						runningRequest = this.$apiGetJ("site/queue-status/" + queueId)
							.then(({data}) => {
								if (data.status === 3) {
									this.archivationInProcess = false;
									this.reloadItem();
									clearInterval(keyInterval);
								}
							})
							.catch(this.$handleErrorWithBuefy)
							.finally(() => runningRequest = null);
					}
				}, 3000);
			}
		},
		created() {
			this.reloadItem();
		}

	};
</script>

<style lang="scss">

</style>