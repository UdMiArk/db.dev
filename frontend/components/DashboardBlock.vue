<template>
	<div>
		<AwaitingResourcesModeratorList v-if="canModerateResources"/>
		<AwaitingResourcesList v-else-if="canCreateResources"/>
	</div>
</template>

<script>
	import {mapState} from "vuex";
	import AwaitingResourcesList from "@components/resources/AwaitingResourcesList";
	import AwaitingResourcesModeratorList from "@components/resources/AwaitingResourcesModeratorList";

	export default {
		name: "DashboardBlock",
		components: {AwaitingResourcesModeratorList, AwaitingResourcesList},
		computed: {
			...mapState("auth", ["permissions"]),
			canCreateResources() {
				return this.permissions.canCreateResources;
			},
			canModerateResources() {
				return this.permissions.canModerateResources;
			}
			/*
			canManageResourceTypes() {
				return this.permissions.canManageResourceTypes;
			},
			canManageUsers() {
				return this.permissions.canManageUsers;
			},
			*/
		}
	};
</script>