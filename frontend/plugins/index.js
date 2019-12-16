import Vue from "vue";
import {deepFreeze, isArray, isPlainObject, objectCopy, objectExtend} from "./object";
import Api from "./api";
import DateTime from "./datetime";
// Buefy
import Buefy from "buefy";
import BuefyConfig from "./buefy";
import "@assets/bulma.scss";
import "@assets/spacing.scss";

Object.deepFreeze = deepFreeze;
Object.isPlain = isPlainObject;
Object.isArray = isArray;
Object.copy = objectCopy;
Object.extend = objectExtend;

Vue.use(Buefy, BuefyConfig);

Object.defineProperty(Vue.prototype, "$handleErrorWithBuefy", {
	get() {
		return (err => {
			console.error(err);
			this.$buefy.notification.open({
				message: err.message || err.toString(),
				type: "is-danger",
				hasIcon: true
			});
		});
	}
});

Vue.use(Api);
Vue.use(DateTime);

function getEnumData(value, enumObj, nullValue = null) {
	return value == null ? nullValue : enumObj.indexed[value];
}

function getEnumLabel(value, enumObj, nullValue = null) {
	const data = getEnumData(value, enumObj);
	return data == null ? nullValue : data.label;
}

Vue.filter("enumData", getEnumData);
Vue.filter("enumLabel", getEnumLabel);