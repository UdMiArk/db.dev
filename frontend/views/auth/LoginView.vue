<template>
	<BaseViewContainer>
		<LoginForm :back="qBack"/>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";
	import LoginForm from "@components/actions/LoginForm";

	export default {
		name: "LoginView",
		components: {LoginForm, BaseViewContainer},
		props: {
			qBack: String
		}
	};
</script>