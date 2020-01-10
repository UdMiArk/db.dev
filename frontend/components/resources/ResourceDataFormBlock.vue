<template>
	<div>
		<p class="help is-danger" v-if="generalErrors">
			<template v-for="(error, idx) in generalErrors"><br v-if="idx"/>{{error}}</template>
		</p>
		<component
				:errors="errors && errors[field.key]"
				:is="field.component"
				:key="field.key"

				:label="field.label"
				:description="field.description"
				:disabled="disabled"
				:value="value && value[field.key]"
				v-for="field in fields"
				:options="field.options"
				:required="field.required"
				@input="setValue(field.key, $event)"
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
				for (const attr of resourceType.typeAttributes) {
					result.push(Object.freeze({
						key: attr.__id,
						label: attr.name,
						component: FIELD_TYPES[attr.type],
						required: attr.required,
						description: attr.description,
						options: attr.options
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