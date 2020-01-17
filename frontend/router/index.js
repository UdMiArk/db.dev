import Vue from "vue";
import VueRouter from "vue-router";
import RoutesAuth from "./routes-auth";
import RoutesResources from "./routes-resources";
import RoutesUsers from "./routes-users";
import RoutesRtypes from "./routes-rtypes";
import HomeView from "@views/HomeView";
import NotFoundView from "@views/NotFoundView";

import Store from "@/store/index.js";
import {onAuthInitialized} from "@/store/module-auth";

Vue.use(VueRouter);

const routes = [
	{
		path: "/",
		name: "home",
		component: HomeView,
		meta: {guestAllowed: true},
		props(route) {
			return {
				qBack: route.query && route.query.back && atob(route.query.back) || undefined
			};
		},
		beforeEnter(to, from, next) {
			/*import('../store/index.js').then(module => {
				const auth = module.default.state.auth;
				if (auth.authenticated && auth.simpleUser) {
					throw new Error("No need for dashboard");
				} else {
					next();
				}
			}).catch(err => {
				next({name: 'resourcesList'});
			});*/
			const auth = Store.state.auth;
			if (auth && auth.authenticated && auth.simpleUser) {
				next({name: "resourcesList"});
			} else {
				next();
			}
		}
	},
	...RoutesAuth,
	...RoutesResources,
	...RoutesUsers,
	...RoutesRtypes,
	{
		path: "*",
		name: "notFound",
		component: NotFoundView
	}
];

const router = new VueRouter({
	mode: "history",
	base: process.env.BASE_URL,
	routes
});

const guardFunction = ((to, from, next) => {
	const _Store = Store;
	const isGuest = !_Store.getters["auth/isLoggedIn"];
	const stack = to.matched;

	if (isGuest) {
		for (let meta, i = stack.length - 1; i >= 0; i--) {
			meta = stack[i].meta;
			if (!meta.guestAllowed) {
				let q = {};
				if (to.path && to.path !== "/") {
					q.back = btoa(to.path);
				}
				if (!Object.keys(q).length) {
					q = undefined;
				}

				next({name: "login", query: q, replace: true});
				return;
			}
		}
	}
	next();
});

router.beforeEach((to, from, next) => {
	onAuthInitialized.then(() => guardFunction(to, from, next));
});

export default router;
