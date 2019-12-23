import {apiFetch} from "@plugins/api";
import {deepFreeze} from "@plugins/object";

const MODULE = {
	namespaced: true,
	state: {
		roles: null,
		isRequestingInfo: false,
		error: null
	},
	mutations: {
		setRoles(state, roles) {
			state.roles = roles;
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
		refreshRoles({commit}) {
			commit("startRequesting");
			return apiFetch("users/roles").then(r => r.json())
				.then(data => (commit("setRoles", deepFreeze(data)), commit("endRequesting")))
				.catch(err => {
					commit("endRequesting", err);
					throw err;
				});
		}
	}
};

export function ensureRegistered($store) {
	if (!$store.state.users) {
		$store.registerModule("users", MODULE);
		$store.dispatch("users/refreshRoles");
	}
}

export default MODULE;