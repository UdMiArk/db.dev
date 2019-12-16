<template>
	<form @submit.prevent="handleSubmit">
		<slot :props="formProps"/>
	</form>
</template>

<script>
	import {deepFreeze, objectExtend} from "@/plugins/object";

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

	const proxyHandler = Object.freeze({
		isExtensible(target) {
			console.log("Proxy.isExtensible(target)", arguments);
			return false;
		},
		has(target, property) {
			//console.log("Proxy.has(target, property)", arguments);
			return target.$props ? target.$props.includes(property) : (property in target.$changes);
		},
		get(target, property, receiver) {
			//console.log("Proxy.get(target, property, receiver)", arguments);
			if (typeof property === "symbol") {
				console.log("Symbol of form data accessed", property);
				return target.$data[property];
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
			//console.log("Proxy.set(target, property, value, receiver)", arguments);
			if (typeof property === "symbol") {
				console.log("Symbol of form data written", property);
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
			//console.log("Proxy.deleteProperty", arguments);
			if (target.$props && !target.$props.includes(property)) {
				throw new TypeError("Unable to access property '" + property + "'");
			}
			target.$delete(target.$changes, property);
		},
		ownKeys(target) {
			//console.log("Proxy.ownKeys", arguments);
			console.log("ownKeys", target.$props, target.$changes);
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

	export default {
		name: "BaseForm",
		props: {
			data: Object,
			watchers: Object,
			allowedProperties: Array,
			initialChanges: Object,
			locked: Boolean,
			stopPropagation: Boolean
		},
		data() {
			return {
				changes: null
			};
		},
		computed: {
			properties() {
				return this.allowedProperties || (this.data && Object.keys(this.data)) || null;
			},
			formProps() {
				if (this.changes != null) {
					return new Proxy({
						$data: this.data,
						$props: this.properties,
						$changes: this.changes,
						$watchers: this.watchers,
						$set: this.$set,
						$delete: this.$delete
					}, this.$proxyHandler);
				}
			}
		},
		methods: {
			handleSubmit(ev) {
				if (this.stopPropagation) {
					ev.stopPropagation();
				}
				if (!this.locked) {
					this.$emit("submit", Object.freeze({
						changes: deepFreeze(this.getChanges())
					}));
				}
			},
			reset() {
				const changes = {};
				if (this.initialChanges) {
					objectExtend(changes, this.initialChanges);
				}
				this.changes = changes;
			},
			getChanges() {
				return objectExtend({}, this.changes, true, true);
			},
			getChangesWithBase() {
				const result = {};
				if (this.data) {
					objectExtend(result, this.data);
				}
				return objectExtend(result, this.changes);
			}
		},
		created() {
			this.$proxyHandler = proxyHandler;
			this.reset();
		}
	};
</script>