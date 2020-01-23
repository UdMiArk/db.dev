<template>
	<BaseViewContainer>
		<div class="box is-warning" v-if="error">{{error.message || error}}</div>
		<Loader v-else-if="item === null"/>
		<div style="position: relative" v-else>
			<h1 class="is-size-4" style="position: absolute; top: -2.7rem;">Ресурс "{{item.name}}"</h1>
			<ResourceDisplay :data="item" @startWaiting="showArchivationProcess(item.archived_queue)" ref="display">
				<template #footer v-if="canDoActions">
					<div class="has-text-right" v-if="canApprove">
						<b-button :disabled="processing" @click="handleDelete" class="is-pulled-left" type="is-warning" v-if="canDelete">Удалить</b-button>
						<b-button :disabled="processing" @click="askForStatusComment = true" type="is-warning">Отклонить</b-button>
						<b-button :disabled="processing" @click="setResourceStatus(true)" type="is-primary ml-sm">Утвердить</b-button>
						<b-modal
								:active.sync="askForStatusComment"
								@close="comment = ''"
								aria-modal
								aria-role="dialog"
						>
							<form @submit.prevent="(setResourceStatus(false, comment), askForStatusComment = false, comment = '')" class="card" style="max-width: 24rem; margin: auto">
								<div class="card-header">
									<span class="card-header-title">Введите (по желанию) причину отказа</span></div>
								<div class="card-content">
									<b-input name="comment" type="textarea" v-model="comment"/>
								</div>
								<div class="card-footer">
									<div class="card-footer-item is-block has-text-right">
										<b-button @click="(askForStatusComment = false, comment = '')" type="is-default">Отменить</b-button>
										<b-button class="ml-sm" native-type="submit" type="is-warning">Отклонить</b-button>
									</div>
								</div>
							</form>
						</b-modal>
					</div>
					<div v-else>
						<b-button :disabled="processing" @click="handleDelete" type="is-warning mr-sm" v-if="canDelete">Удалить</b-button>
						<b-button :disabled="processing" @click="askForArchivedComment = true" v-if="canSendToArchive">Отправить в архив</b-button>
						<b-button :disabled="processing" @click="returnFromArchive" v-if="canReturnFromArchive">Вернуть из архива</b-button>
						<b-modal
								:active.sync="askForArchivedComment"
								@close="comment = ''"
								aria-modal
								aria-role="dialog"
								v-if="canSendToArchive"
						>
							<form @submit.prevent="(sendToArchive(comment), askForArchivedComment = false, comment = '')" class="card" style="max-width: 24rem; margin: auto">
								<div class="card-header"><span class="card-header-title">Комментарий к архивации?</span>
								</div>
								<div class="card-content">
									<b-input name="comment" type="textarea" v-model="comment"/>
								</div>
								<div class="card-footer">
									<div class="card-footer-item is-block has-text-right">
										<b-button @click="(askForArchivedComment = false, comment = '')" type="is-default">Отменить</b-button>
										<b-button class="ml-sm" native-type="submit" type="is-primary">Отправить в архив</b-button>
									</div>
								</div>
							</form>
						</b-modal>
					</div>
				</template>
			</ResourceDisplay>
		</div>
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
	import {processErrors} from "@plugins/form";

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
				archivationInProcess: false,
				processing: false,

				askForStatusComment: false,
				askForArchivedComment: false,
				comment: ""
			};
		},
		computed: {
			...mapState("auth", ["permissions"]),
			canDoActions() {
				return this.canApprove || this.canSendToArchive || this.canReturnFromArchive || this.canDelete;
			},
			canSendToArchive() {
				return this.item?.canArchive && !this.item.archived;
			},
			canReturnFromArchive() {
				return this.item?.canArchive && this.item.archived;
			},
			canApprove() {
				return this.item?.canApprove;
			},
			canDelete() {
				return this.item?.canDelete;
			}
		},
		methods: {
			showLoading() {
				return this.$buefy.loading.open({container: this.$refs.display.$el});
			},
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

			setResourceStatus(approved, comment = undefined) {
				const processing = this.showLoading();
				this.processing = true;
				return (
					this.$apiPostJ("resources/set-status/" + this.qPk, {approved: approved, comment})
						.then(({data}) => {
							if (data.success) {
								this.item = deepFreeze(data.resource);
							} else {
								throw new Error(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch(this.$handleErrorWithBuefy)
						.finally(() => (this.processing = false, processing.close()))
				);
			},
			sendToArchive(comment = undefined) {
				const processing = this.showLoading();
				return (
					this.$apiPostJ("resources/archive/" + this.qPk, comment ? {comment} : undefined)
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
				const processing = this.showLoading();
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
			},
			handleDelete() {
				if (this.canDelete) {
					this.$buefy.dialog.confirm({
						title: "Удаление ресурса",
						message: "Вы уверены что хотите <b>удалить</b> ресурс '" + this.item.name + "'? Удаленный ресурс не подлежит востановлению.",
						confirmText: "Удалить",
						cancelText: "Отмена",
						type: "is-danger",
						hasIcon: true,
						onConfirm: () => {
							const processDisplay = this.showLoading();
							this.processing = true;
							this.error = undefined;
							this.$apiPostJ("resources/delete/" + this.qPk)
								.then(({data}) => {
									if (data.success) {
										this.$router.push({name: "resourcesList"});
									} else if (data.errors) {
										this.errors = deepFreeze(processErrors(data.errors));
									} else {
										this.$handleErrorWithBuefy(data.error || "Не удалось прочитать ответ сервера");
									}
								})
								.catch(this.$handleErrorWithBuefy)
								.finally(() => (this.processing = false, processDisplay.close()));
						}
					});
				}
			}
		},
		created() {
			this.reloadItem();
		}

	};
</script>

<style lang="scss">

</style>