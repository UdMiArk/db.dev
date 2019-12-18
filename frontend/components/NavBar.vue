<template>
	<b-navbar class="is-unselectable" shadow>
		<template #brand v-if="brand">
			<b-navbar-item :to="brand.link" tag="router-link">
				<img
						:alt="brand.alt"
						:src="brand.src"
				/>
			</b-navbar-item>
		</template>
		<template #start v-if="navItems && navItems.length">
			<template v-for="item in navItems">
				<b-navbar-dropdown :key="item.label" v-if="item.children">
					<template #label>
						<b-icon :icon="item.icon" class="mr-xs" v-if="item.icon"/>
						<span> {{item.label}}</span></template>
					<b-navbar-item :href="subitem.href" :key="subitem.label" :tag="subitem.route ? 'router-link' : undefined" :to="subitem.route" v-for="subitem in item.children">
						<b-icon :icon="subitem.icon" class="mr-xs" v-if="subitem.icon"/>
						<span> {{subitem.label}}</span>
					</b-navbar-item>
				</b-navbar-dropdown>
				<b-navbar-item :active="isActive(item)" :href="item.href" :key="item.label" :tag="item.route ? 'router-link' : undefined" :to="item.route" v-else>
					<b-icon :icon="item.icon" class="mr-xs" v-if="item.icon"/>
					<span> {{item.label}}</span>
				</b-navbar-item>
			</template>
		</template>
		<template #end v-if="isLoggedIn">
			<b-navbar-item tag="span">{{userName}}</b-navbar-item>
			<b-navbar-item @click="handleLogout">Выход</b-navbar-item>
		</template>
	</b-navbar>
</template>

<script>
	import {mapGetters, mapMutations} from "vuex";

	export default {
		name: "NavBar",
		props: {
			brand: Object,
			navItems: Array
		},
		computed: {
			...mapGetters("auth", ["isLoggedIn", "userName"])
		},
		methods: {
			...mapMutations("auth", ["setAuthData"]),
			handleLogout(ev) {
				this.doLogout()
					.then(({data}) => {
						this.setAuthData(data.auth);
						if (!data.success) {
							return Error(data.error || "Не удалось выйти из приложения");
						}
						this.$router.push({name: "home"});
					})
					.catch(this.$handleErrorWithBuefy);
			},
			isActive(item) {
				return item.route && item.route.name === this.$route.name;
			},
			doLogout() {
				return this.$apiPostJ("auth/logout");
			}
		}
	};
</script>

<style lang="scss">

</style>