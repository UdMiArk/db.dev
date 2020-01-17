<template>
	<BaseViewContainer>
		<DashboardBlock v-if="isLoggedIn"/>
		<LoginForm :back="qBack" v-else/>
	</BaseViewContainer>
</template>

<script>
	import {mapGetters} from "vuex";
	import BaseViewContainer from "@components/BaseViewContainer";
	import LoginForm from "@components/actions/LoginForm";
	import DashboardBlock from "@components/DashboardBlock";

	export default {
		name: "HomeView",
		components: {DashboardBlock, LoginForm, BaseViewContainer},
		props: {
			qBack: String
		},
		computed: {
			...mapGetters("auth", ["isLoggedIn", "userName"])
		}
	};
</script>

<style lang="scss">

</style>