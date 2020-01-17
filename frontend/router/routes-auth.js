import LoginView from "@views/auth/LoginView";

export default [
	{
		path: "/login",
		name: "login",
		component: LoginView,
		meta: {guestAllowed: true},
		props(route) {
			return {
				qBack: route.query && route.query.back && atob(route.query.back) || undefined
			};
		}
	}
];