import Vue from "vue";
import VueRouter from "vue-router";
import RoutesAuth from "./routes-auth";
import RoutesResources from "./routes-resources";
import HomeView from "@views/HomeView";
import NotFoundView from "@views/NotFoundView";

Vue.use(VueRouter);

const routes = [
	{
		path: "/",
		name: "home",
		component: HomeView
	},
	...RoutesAuth,
	...RoutesResources,
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

export default router;
