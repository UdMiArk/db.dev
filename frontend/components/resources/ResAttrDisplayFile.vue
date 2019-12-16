<template>
	<b-field :label="label">
		<span class="input">
			<a :href="fileLink" class="component" download target="_blank" v-if="value">{{value.name}}</a>
		</span>
	</b-field>
</template>

<script>
	import {getApiResourceFileLink} from "@plugins/api";

	export default {
		name: "ResAttrDisplayFile",
		props: {
			label: String,
			value: {},
			resource: Object
		},
		computed: {
			fileLink() {
				if (!this.value) {
					return undefined;
				}
				return getApiResourceFileLink(this.value, this.resource.__id);
			}
		}
	};
</script>

<style lang="scss">

</style>