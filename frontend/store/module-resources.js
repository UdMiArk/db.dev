import {apiFetch} from "@plugins/api";
import {deepFreeze} from "@plugins/object";

const MODULE = {
	namespaced: true,
	state: {
		existingTypes: null,
		isRequestingInfo: false,
		error: null
	},
	mutations: {
		setExistingTypes(state, types) {
			state.existingTypes = types;
		},
		startRequesting(state) {
			state.isRequestingInfo = true;
			state.error = null;
		},
		endRequesting(state, error = null) {
			state.isRequestingInfo = false;
			state.error = error;
		}
	},
	actions: {
		refreshExistingTypes({commit}) {
			commit("startRequesting");
			return apiFetch("resources/existing-types").then(r => r.json())
				.then(data => (commit("setExistingTypes", deepFreeze(data)), commit("endRequesting")))
				.catch(err => {
					commit("endRequesting", err);
					throw err;
				});
		}
	}
};
export const MODULE_NAME = "resources";

export function ensureRegistered($store) {
	if (!$store.state[MODULE_NAME]) {
		$store.registerModule(MODULE_NAME, MODULE);
		$store.dispatch(MODULE_NAME + "/refreshExistingTypes");
	}
}

export default MODULE;