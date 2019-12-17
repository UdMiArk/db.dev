export default [
	{
		path: "/users",
		name: "usersList",
		component: () => import(/* webpackChunkName: "route-users" */ "@views/users/UserListView")
	},
	{
		path: "/users/register",
		name: "userRegister",
		component: () => import(/* webpackChunkName: "route-users" */ "@views/users/UserRegisterView")
	},
	{
		path: "/users/:qPk",
		name: "userView",
		component: () => import(/* webpackChunkName: "route-users" */ "@views/users/UserView"),
		props: true
	}
];