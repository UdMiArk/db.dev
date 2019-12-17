<template>
	<BaseViewContainer>
		<UserRegisterForm @success="handleCreationSuccess"/>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import UserRegisterForm from "@components/actions/users/RegisterForm";

	export default {
		name: "UserRegisterView",
		components: {UserRegisterForm, BaseViewContainer},
		methods: {
			handleCreationSuccess(data) {
				this.$router.replace({name: "userView", params: {qPk: data.__id.toString()}});
			}
		}
	};
</script>

<style lang="scss">

</style>