/**
 * Proxy traps:
		getPrototypeOf(target),
		setPrototypeOf(target, prototype),
		isExtensible(target),
		preventExtensions(target),
		getOwnPropertyDescriptor(target, property),
		defineProperty(target, property, descriptor),
		has(target, property),
		get(target, property, receiver),
		set(target, property, value, receiver),
		deleteProperty(target, property),
		ownKeys(target),
		apply(target, thisArg, argumentsList),
		construct(target, argumentsList, newTarget),
 */

/**
 * Expected proxy target:
 * {
 *  $data: this.data,			- original data object
 *  $props: this.properties,	- list of available props, if not defined Object.keys($data)
 *  $changes: this.changes,		- changed data object, send to $set method
 *  $watchers: this.watchers,	- {property: (({set(prop, value), get(prop)}, newValue, oldValue, proxyTarget) => void)}, dict of methods called on property change
 *  $set: this.$set,			- ($changes, property, value) => void, function called to apply new prop value to $changes
 *  $delete: this.$delete		- ($changes, property) => void, function called to remove prop value from $changes
 * }
 */
export const proxyHandler = Object.freeze({
	isExtensible(target) {
		return false;
	},
	has(target, property) {
		return target.$props ? target.$props.includes(property) : (property in target.$changes);
	},
	get(target, property, receiver) {
		if (typeof property === "symbol") {
			return (target.$data || target.$changes || {})[property];
		}
		if (target.$props && !target.$props.includes(property)) {
			// Vue Devtools plugin wont show data if error thrown on some properties
			//throw new TypeError("Unable to access property '" + property + "'");
			return undefined;
		}
		if (target.$changes.hasOwnProperty(property)) {
			return target.$changes[property];
		}
		return (target.$data || {})[property];
	},
	set(target, property, value, receiver) {
		if (typeof property === "symbol") {
			target.$changes[property] = value;
			return true;
		}
		if (target.$props && !target.$props.includes(property)) {
			throw new TypeError("Unable to access property '" + property + "'");
		}
		const oldValue = this.get(target, property, receiver);

		target.$set(target.$changes, property, value);
		const watcher = target.$watchers?.[property];
		watcher && watcher.call({
			set: ((prop, value) => this.set(target, prop, value, receiver)),
			get: (prop => this.get(target, prop, receiver))
		}, value, oldValue, target);
		return true;
	},
	deleteProperty(target, property) {
		if (target.$props && !target.$props.includes(property)) {
			throw new TypeError("Unable to access property '" + property + "'");
		}
		target.$delete(target.$changes, property);
	},
	ownKeys(target) {
		return target.$props || Object.keys(target.$changes);
	},
	getOwnPropertyDescriptor(target, property) {
		if (target.$props && !target.$props.includes(property)) {
			return undefined;
		}
		return {
			enumerable: true,
			configurable: true
		};
	}
	/*getPrototypeOf(target) {
		console.log("Proxy.getPrototypeOf(target)", target);
		return Object.getPrototypeOf(target.$changes);
	},
	setPrototypeOf(target, prototype) {
		console.log("Proxy.setPrototypeOf(target, prototype)", arguments);
		throw new TypeError("Unable to change prototype");
	},
	isExtensible(target) {
		console.log("Proxy.isExtensible(target)", arguments);
		return false;
	},
	preventExtensions(target) {
		console.log("Proxy.preventExtensions(target)", arguments);
	},
	defineProperty(target, property, descriptor) {
		console.log("Proxy.defineProperty(target, property, descriptor)", arguments);
		throw new TypeError("Unable to redefine properties");
	},
	apply(target, thisArg, argumentsList) {
		//console.log("Proxy.apply", arguments);
		throw new TypeError("Object is not a function");
	},
	construct(target, argumentsList, newTarget) {
		//console.log("Proxy.construct", arguments);
		throw new TypeError("Object is not a constructor");
	}*/
});


export function processErrors(errors) {
	const result = {},
		resultMap = {},
		errKeys = Object.keys(errors);
	errKeys.sort();

	for (const key of errKeys) {
		let prefix = "",
			lastPropErrors = result;
		const
			props = key.split("."),
			lastPart = props.pop();
		for (const prop of props) {
			const propKey = prefix + prop;
			if (!resultMap[propKey]) {
				resultMap[propKey] = lastPropErrors[prop] = [];
			}
			lastPropErrors = resultMap[propKey];
			prefix = propKey + ".";
		}
		resultMap[prefix + lastPart] = lastPropErrors[lastPart] = typeof errors[key] === "string" ? [errors[key]] : errors[key].slice();
	}

	return result;
}