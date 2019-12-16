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
				<b-navbar-dropdown :key="item.label" :label="item.label" v-if="item.children">
					<b-navbar-item :href="item.href" :tag="item.route ? 'router-link' : undefined" :to="item.route">{{item.label}}</b-navbar-item>
				</b-navbar-dropdown>
				<b-navbar-item :href="item.href" :key="item.label" :tag="item.route ? 'router-link' : undefined" :to="item.route" v-else>{{item.label}}</b-navbar-item>
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
			brand: String,
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
			doLogout() {
				return this.$apiPostJ("auth/logout");
			}
		}
	};
</script>

<style lang="scss">

</style>