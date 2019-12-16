<template>
	<div id="app">
		<template v-if="isInitialized">
			<AppHeader/>
			<AppContent/>
			<AppFooter/>
		</template>
		<div :class="['pageloader is-light', {'is-active': !isInitialized}]" key="pageloader" v-if="showPageloader">
			<span class="title">Приложение загружается..</span></div>
	</div>
</template>

<script>
	import {mapActions, mapGetters} from "vuex";
	import AppHeader from "./AppHeader.vue";
	import AppContent from "./AppContent.vue";
	import AppFooter from "./AppFooter.vue";

	export default {
		name: "App",
		components: {AppFooter, AppContent, AppHeader},
		data() {
			return {
				showPageloader: true
			};
		},
		methods: {
			...mapActions("auth", ["refreshAuthData"])
		},
		computed: {
			...mapGetters("auth", ["isInitialized"])
		},
		watch: {
			isInitialized(newVal) {
				if (newVal) {
					setTimeout(() => this.showPageloader = false, 350);
				} else {
					this.showPageloader = true;
				}
			}
		},
		created() {
			this.refreshAuthData();
		}
	};
</script>