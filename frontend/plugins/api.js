import {isPlainObject} from "@/plugins/object";
import {formatServerDate} from "@plugins/datetime";

export function encodeURIObject(query) {
	return Object.keys(query).filter(x => query[x] !== undefined).map(x => encodeURIComponent(x) + "=" + encodeURIComponent(query[x])).join("&");
}

function getApiUrl(uri, query) {
	return "/api/" + uri + (query ? "?" + encodeURIObject(query) : "");
}

export function getApiResourceFileLink(fileData, resourceId, inline = false) {
	return getApiUrl(
		"storage/resource-file/" + resourceId,
		{
			name: fileData.name,
			inline: inline ? 1 : undefined
		}
	);
}

export function apiFetch(uri, method, query, data, isJson) {
	let url = getApiUrl(uri, query),
		options = {
			method: method || "GET",
			headers: {},
			credentials: "include"
		};

	if (data) {
		if (isPlainObject(data)) {
			if (isJson) {
				options.headers["Content-Type"] = "application/json; charset=UTF-8";
				options.body = JSON.stringify(data);
			} else {
				options.headers["Content-Type"] = "application/x-www-form-urlencoded";
				options.body = encodeURIObject(data);
			}
		} else if (data instanceof FormData) {
			options.body = data;
		} else if (typeof data === "string") {
			options.headers["Content-Type"] = "application/x-www-form-urlencoded";
			options.body = data;
		} else {
			console.error("Unknown type of request data body:", data);
			options.body = data;
		}
	}

	return window.fetch(url, options);
}

/**
 *
 * @param {FormData} formData
 * @param {*} dataObj
 * @param {string} rootName
 * @param {string[]} [ignoreList]
 *
 * @return {FormData}
 */
export function appendObjToFormData(formData, dataObj, rootName, ignoreList = null) {
	if (dataObj !== undefined && !(ignoreList && ignoreList.includes(rootName))) {
		if (Array.isArray(dataObj)) {
			for (let i = 0, iMax = dataObj.length; i < iMax; i++) {
				appendObjToFormData(formData, dataObj[i], rootName + "[" + i + "]", ignoreList);
			}
		} else if (isPlainObject(dataObj)) {
			for (const key of Object.keys(dataObj)) {
				if (rootName) {
					appendObjToFormData(formData, dataObj[key], rootName + "[" + key + "]", ignoreList);
				} else {
					appendObjToFormData(formData, dataObj[key], key, ignoreList);
				}
			}
		} else if (dataObj instanceof Date) {
			formData.append(rootName, formatServerDate(dataObj));
		} else if (dataObj instanceof File) {
			formData.append(rootName, dataObj, dataObj.name);
		} else {
			formData.append(rootName, dataObj);
		}
	}
	return formData;
}

/**
 *
 * @param {string|object} data
 * @param {string} [rootName]
 * @param {string[]} [ignoreList]
 *
 * @return {FormData}
 */
export function jsonToFormData(data, rootName = null, ignoreList = null) {
	return appendObjToFormData(
		new FormData(),
		(typeof data === "string")
			? JSON.parse(data)
			: data,
		rootName || "",
		ignoreList
	);
}

export function jsonFetchHandler(response) {
	if (response.ok) {
		return response.json().then(data => Object.freeze({data, response}));
	} else {
		return response.clone().json().catch(err => response.clone().text()).then(data => {
			const error = Error(data.error && data.error.message || response.statusText);
			error.data = data;
			error.response = response;
			throw error;
		});
	}
}

const CSRF_TOKEN_PROP = "_csrf";

export default Object.freeze({
	encodeURIObject,
	apiFetch,
	jsonToFormData,
	appendObjToFormData,
	install(Vue, options) {
		Vue.prototype.$apiGet = (function $apiGet(uri, query = null) {
			return apiFetch(uri, "GET", query);
		});
		Vue.prototype.$apiPost = (function $apiPost(uri, body = null, query = null) {
			let newBody = body;

			const csrfToken = this.$CSRFToken,
				isJson = isPlainObject(newBody);

			if (csrfToken) {
				if (!newBody) {
					newBody = "";
				}
				if (isJson) {
					newBody = Object.assign({}, newBody);
					newBody[CSRF_TOKEN_PROP] = csrfToken;
				} else if (typeof newBody === "string") {
					newBody =
						encodeURIComponent(CSRF_TOKEN_PROP)
						+ "=" + encodeURIComponent(csrfToken)
						+ (newBody.length ? "&" + newBody : "");
				} else if (newBody instanceof FormData) {
					newBody.set(CSRF_TOKEN_PROP, csrfToken);
				} else {
					console.warn("CSRF Token found but not set due to unknown type of post body:", body);
				}
			} else {
				console.warn("CSRF Token not found so post request to API will probably fail");
			}
			return apiFetch(uri, "POST", query, newBody, isJson);
		});
		Vue.prototype.$apiGetJ = (function $apiGetJ(uri, query = null) {
			return this.$apiGet(uri, query).then(jsonFetchHandler);
		});
		/**
		 *
		 * @type {function(string=, object=, object=): Q.Promise<any> | PromiseLike<any>}
		 */
		Vue.prototype.$apiPostJ = (function $apiPostJ(uri, body = null, query = null) {
			return this.$apiPost(uri, body, query).then(jsonFetchHandler);
		});
		Object.defineProperty(Vue.prototype, "$CSRFToken", {
			get() {
				return this.$store?.state?.auth?.csrfToken;
			}
		});
	}
});