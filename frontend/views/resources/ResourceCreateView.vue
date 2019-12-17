<template>
	<BaseViewContainer>
		<div class="box is-warning" v-if="error">{{error}}</div>
		<Loader v-if="creationData === null"/>
		<ScAuthForm @success="handleScAuthSuccess" v-else-if="creationData.needScAuth"/>
		<div v-else-if="creationData.noProductsAvailable">
			У вас отсутствуют доступные для управления объекты продвижения.
			Вы можете создать их <a :href="linkCreateProduct" target="_blank">по ссылке</a>.
		</div>
		<ResourceCreateForm :creationData="creationData" @success="handleCreationSuccess" v-else/>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import Loader from "@components/Loader";
	import ScAuthForm from "@components/actions/ScAuthForm";
	import {deepFreeze} from "@/plugins/object";
	import ResourceCreateForm from "@components/actions/resources/CreateForm";

	export default {
		name: "ResourceCreateView",
		components: {ResourceCreateForm, Loader, BaseViewContainer, ScAuthForm},
		data() {
			return {
				creationData: null,
				error: null,
				formErrors: undefined,
				processing: null
			};
		},
		computed: {
			linkCreateProduct() {
				return "https://erp.db.dev/promo-programs/";
			}
		},
		methods: {
			reloadCreationData() {
				this.error = null;
				this.creationData = null;
				return (
					this.$apiGetJ("resources/creation-data")
						.then(({data}) => this.creationData = deepFreeze(data))
						.catch(err => {
							this.error = err;
							this.$handleErrorWithBuefy(err);
						})
				);
			},
			handleScAuthSuccess(data) {
				this.creationData = data;
			},
			handleCreationSuccess(data) {
				this.$router.replace({name: "resourceView", params: {qPk: data.__id.toString()}});
			}
		},
		created() {
			this.reloadCreationData();
		}

	};
</script>

<style lang="scss">

</style>