import Vue from "vue";
import App from "./components/App.vue";
import router from "./router";
import store from "./store";

Vue.config.productionTip = false;

new Vue({
	...App,
	router,
	store
}).$mount("#app");
