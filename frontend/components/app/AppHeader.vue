<template>
	<NavBar :brand="brand" :navItems="navItems"/>
</template>

<script>
	import NavBar from "@components/NavBar";
	import {mapState} from "vuex";

	export default {
		name: "AppHeader",
		components: {NavBar},
		computed: {
			...mapState("auth", ["permissions"]),
			brand() {
				return Object.freeze({
					link: {name: "home"},
					src: require("@assets/images/logo.png"),
					alt: "DB.DEV"
				});
			},
			navItems() {
				const result = [],
					permissions = this.permissions;
				if (permissions.canViewResources) {
					result.push(Object.freeze({
						icon: "database-search",
						label: "Ресурсы",
						route: {name: "resourcesList"}
					}));
				}
				if (permissions.canManageUsers) {
					result.push(Object.freeze({
						icon: "account-group",
						label: "Пользователи",
						route: {name: "usersList"}
					}));
				}
				return Object.freeze(result);
			}
		}
	};
</script>

<style lang="scss">

</style>