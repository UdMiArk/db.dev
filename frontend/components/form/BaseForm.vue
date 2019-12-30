<template>
	<form @submit.prevent="handleSubmit">
		<slot :changes="changes" :props="formProps"/>
	</form>
</template>

<script>
	import {deepFreeze, objectExtend} from "@/plugins/object";
	import {proxyHandler} from "@plugins/form";

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
		beforeCreate() {
			this.$proxyHandler = proxyHandler;
		},
		created() {
			this.reset();
		}
	};
</script>