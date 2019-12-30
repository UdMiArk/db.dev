<template>
	<div>
		<component
				:is="field.component"
				:key="field.key"
				:label="field.label"
				:required="field.required"
				:resource="resource"
				:value="value && value[field.key]"
				v-for="field in fields"
		/>
	</div>
</template>

<script>
	import {RESATTR_TYPES} from "@/data/resources";
	import ResAttrDisplayFile from "@components/resources/ResAttrDisplayFile";
	import ResAttrDisplayFiles from "@components/resources/ResAttrDisplayFiles";

	const FIELD_TYPES = Object.freeze({
		[RESATTR_TYPES.FILE]: ResAttrDisplayFile,
		[RESATTR_TYPES.FILES]: ResAttrDisplayFiles
	});

	export default {
		name: "ResourceDataDisplayBlock",
		props: {
			resourceType: Object,
			value: Object,
			resource: Object
		},
		computed: {
			fields() {
				const
					resourceType = this.resourceType,
					result = [];
				for (const attr of resourceType.typeAttributes) {
					result.push(Object.freeze({
						key: attr.__id,
						label: attr.name,
						component: FIELD_TYPES[attr.type],
						description: attr.description
					}));
				}
				return Object.freeze(result);
			}
		}
	};
</script>