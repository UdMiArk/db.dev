<template>
	<BaseViewContainer>
		<RtypeFormBlock :errors="errors" :item="defaultValues" :processing="processing" @submit="handleSubmit">
			<template #header>
				<span class="card-header-title">Создание типа ресурса</span>
			</template>
		</RtypeFormBlock>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import UserRegisterForm from "@components/actions/users/RegisterForm";
	import {deepFreeze} from "@plugins/object";
	import RtypeFormBlock from "@components/rtypes/RtypeFormBlock";
	import {processErrors} from "@plugins/form";

	export default {
		name: "RtypeCreateView",
		components: {RtypeFormBlock, UserRegisterForm, BaseViewContainer},
		data() {
			return {
				processing: false,
				errors: null
			};
		},
		computed: {
			defaultValues() {
				return Object.freeze({
					name: "",
					group_name: "",
					responsible_id: null,
					description_link: "",
					description: "",
					typeAttributes: null
				});
			}
		},
		methods: {
			handleSubmit({changes}) {
				const processDisplay = this.$buefy.loading.open({container: this.$refs.display?.$el || this.$el});
				this.processing = true;
				this.errors = undefined;
				this.$apiPostJ("resource-types/create", changes)
					.then(({data}) => {
						if (data.success) {
							this.handleCreationSuccess(deepFreeze(data));
						} else if (data.errors) {
							this.errors = deepFreeze(processErrors(data.errors));
						} else {
							this.$handleErrorWithBuefy(data.error || "Не удалось прочитать ответ сервера");
						}
					})
					.catch(this.$handleErrorWithBuefy)
					.finally(() => (this.processing = false, processDisplay.close()));
			},
			handleCreationSuccess(data) {
				this.$router.replace({name: "rtypeView", params: {qPk: data.__id.toString()}});
			}
		}
	};
</script>

<style lang="scss">

</style>