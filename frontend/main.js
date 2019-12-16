import Vue from "vue";
import router from "./router";
import store from "./store";
import "@plugins";
import "@assets/index.scss";
import App from "@components/app/App.vue";

Vue.config.productionTip = false;

new Vue({
	...App,
	router,
	store
}).$mount("#app");
