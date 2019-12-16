<template>
	<div>
		<p class="help is-danger" v-if="generalErrors">
			<template v-for="(error, idx) in generalErrors"><br v-if="idx"/>{{error}}</template>
		</p>
		<component
				:disabled="disabled"
				:errors="errors && errors[field.key]"
				:is="field.component"
				:key="field.key"
				:label="field.label"
				:required="field.required"
				:value="value && value[field.key]"
				@input="setValue(field.key, $event)"
				v-for="field in fields"
		/>
	</div>
</template>

<script>
	import {RESATTR_TYPES} from "@/data/resources";
	import ResAttrFieldFile from "@components/resources/ResAttrFieldFile";
	import ResAttrFieldFiles from "@components/resources/ResAttrFieldFiles";

	const FIELD_TYPES = Object.freeze({
		[RESATTR_TYPES.FILE]: ResAttrFieldFile,
		[RESATTR_TYPES.FILES]: ResAttrFieldFiles
	});

	export default {
		name: "ResourceDataFormBlock",
		props: {
			resourceType: Object,
			value: Object,
			errors: Object,
			disabled: Boolean
		},
		computed: {
			generalErrors() {
				const errors = this.errors && this.errors[""];
				if (typeof errors === "string") {
					return Object.freeze([errors]);
				}
				return errors;
			},
			fields() {
				const
					resourceType = this.resourceType,
					result = [];
				for (const attr of resourceType.attributes) {
					result.push(Object.freeze({
						key: attr.__id,
						label: attr.name,
						component: FIELD_TYPES[attr.type],
						required: attr.required,
						description: attr.description
					}));
				}
				return Object.freeze(result);
			}
		},
		methods: {
			setValue(fieldKey, fieldValue) {
				const value = this.value ? Object.assign({}, this.value) : {};
				if (fieldValue === undefined) {
					delete value[fieldKey];
				} else {
					value[fieldKey] = fieldValue;
				}
				this.$emit("input", Object.freeze(value));
			}
		},
		created() {

		}
	};
</script>

<style lang="scss">

</style>