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
	getters: {
		existingTypeGroups({existingTypes}) {
			if (!existingTypes) {
				return null;
			}
			const resultMap = {};
			for (const item of existingTypes) {
				if (!resultMap[item.group_name]) {
					resultMap[item.group_name] = [];
				}
				resultMap[item.group_name].push(item);
			}
			return Object.freeze(Object.keys(resultMap).sort().map(x => Object.freeze({
				name: x,
				items: Object.freeze(resultMap[x])
			})));
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