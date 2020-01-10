<template>
	<b-field :label="label">
		<ul class="list" v-if="value">
			<li class="list-item">
				<span v-if="resourceArchived">{{value.name}}</span>
				<a :href="fileLink" class="component" download target="_blank" v-else>{{value.name}}</a>
			</li>
		</ul>
		<span class="input" v-else/>
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
			},
			resourceArchived() {
				return !!this.resource.archived;
			}
		}
	};
</script>

<style lang="scss">

</style>