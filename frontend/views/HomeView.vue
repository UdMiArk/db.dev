<template>
	<BaseViewContainer>
		<div v-if="isLoggedIn">Привет, {{userName}}</div>
		<LoginForm v-else/>
	</BaseViewContainer>
</template>

<script>
	import {mapGetters} from "vuex";
	import BaseViewContainer from "@components/BaseViewContainer";
	import LoginForm from "@components/actions/LoginForm";

	export default {
		name: "HomeView",
		components: {LoginForm, BaseViewContainer},
		computed: {
			...mapGetters("auth", ["isLoggedIn", "userName"])
		}
	};
</script>

<style lang="scss">

</style>