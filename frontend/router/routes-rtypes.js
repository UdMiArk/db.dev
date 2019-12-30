export default [
	{
		path: "/rtypes",
		name: "rtypesList",
		component: () => import(/* webpackChunkName: "route-rtypes" */ "@views/rtypes/RtypeListView")
	},
	{
		path: "/rtypes/create",
		name: "rtypeCreate",
		component: () => import(/* webpackChunkName: "route-rtypes" */ "@views/rtypes/RtypeCreateView")
	},
	{
		path: "/rtypes/:qPk",
		name: "rtypeView",
		component: () => import(/* webpackChunkName: "route-rtypes" */ "@views/rtypes/RtypeView"),
		props: true
	}
];