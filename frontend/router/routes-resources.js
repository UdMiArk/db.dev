import ListView from "@views/resources/ResourceListView";
import CreateView from "@views/resources/ResourceCreateView";
import DisplayView from "@views/resources/ResourceView";

export default [
	{
		path: "/resources",
		name: "resourcesList",
		component: ListView
	},
	{
		path: "/resources/create",
		name: "resourceCreate",
		component: CreateView
	},
	{
		path: "/resources/:qPk",
		name: "resourceView",
		component: DisplayView,
		props: true
	}
];