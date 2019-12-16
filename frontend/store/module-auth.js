import {apiFetch} from "@/plugins/api";

export default {
	namespaced: true,
	state: {
		csrfToken: null,
		domains: null,
		user: null,
		permissions: Object.freeze({}),
		authenticated: null,
		isRequestingInfo: false,
		error: null
	},
	mutations: {
		setCsrfToken(state, token) {
			state.csrfToken = token;
		},
		setAuthData(state, data) {
			state.csrfToken = data.csrfToken;
			state.user = data.authenticated ? Object.freeze(data.user) : null;
			state.permissions = Object.freeze(data.permissions || {});
			state.domains = Object.freeze(data.domains || []);
			state.authenticated = !!state.user;
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
		refreshAuthData({commit}) {
			commit("startRequesting");
			return apiFetch("auth").then(r => r.json())
				.then(data => (commit("setAuthData", data), commit("endRequesting")))
				.catch(err => {
					commit("endRequesting", err);
					throw err;
				});
		}
	},
	getters: {
		isInitialized({authenticated}) {
			return authenticated != null;
		},
		isLoggedIn({authenticated}) {
			return authenticated === true;
		},
		userName({user}) {
			return user ? user.name : "Гость";
		},
		userId({user}) {
			return user ? user.__id : null;
		}
	}
};