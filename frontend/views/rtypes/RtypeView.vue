<template>
	<BaseViewContainer>
		<div class="box is-warning" v-if="error">{{error.message || error}}</div>
		<RtypeFormBlock :errors="errors" :item="data" :processing="processing" :readonly="!editable" @submit="handleSubmit" ref="display">
			<template #header v-if="item">
				<span class="card-header-title">Тип ресурса "{{data.name}}"</span>
				<span class="card-header-icon">
					<b-switch title="Режим редактирования" v-model="editable"/>
				</span>
			</template>
			<template #controls v-if="editable">
				<div class="has-text-right">
					<b-button :loading="processing" native-type="submit" type="is-primary">Сохранить</b-button>
				</div>
			</template>
		</RtypeFormBlock>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import {deepFreeze} from "@/plugins/object";
	import Loader from "@components/Loader";
	import RtypeFormBlock from "@components/rtypes/RtypeFormBlock";
	import {processErrors} from "@plugins/form";

	export default {
		name: "RtypeView",
		components: {RtypeFormBlock, Loader, BaseViewContainer},
		props: {
			qPk: String
		},
		data() {
			return {
				item: null,
				errors: undefined,
				error: null,
				editable: false,
				processing: false
			};
		},
		computed: {
			data() {
				return this.item || Object.freeze({});
			}
		},
		methods: {
			showLoading() {
				return this.$buefy.loading.open({container: this.$refs.display?.$el || this.$el});
			},

			reloadItem() {
				this.error = null;
				this.item = null;
				const processing = this.showLoading();
				return (
					this.$apiGetJ("resource-types/view/" + this.qPk)
						.then(({data}) => this.item = deepFreeze(data))
						.catch(err => {
							this.error = err;
							this.$handleErrorWithBuefy(err);
						}).finally(() => processing.close())
				);
			},

			handleSubmit({changes}) {
				if (Object.keys(changes).length) {
					const processDisplay = this.showLoading();
					this.processing = true;
					this.errors = undefined;
					this.$apiPostJ("resource-types/update/" + this.qPk, changes)
						.then(({data}) => {
							if (data.success) {
								this.handleSubmitSuccess(deepFreeze(data));
							} else if (data.errors) {
								this.errors = deepFreeze(processErrors(data.errors));
							} else {
								this.$handleErrorWithBuefy(data.error || "Не удалось прочитать ответ сервера");
							}
						})
						.catch(this.$handleErrorWithBuefy)
						.finally(() => (this.processing = false, processDisplay.close()));
				} else {
					this.editable = false;
				}
			},
			handleSubmitSuccess(data) {
				this.editable = false;
				this.item = data.data;
			}
		},
		watch: {
			editable(newValue) {
				if (!newValue) {
					this.$refs.display.reset();
				}
			}
		},
		mounted() {
			this.reloadItem();
		}
	};
</script>
