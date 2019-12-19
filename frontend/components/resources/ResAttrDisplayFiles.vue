<template>
	<b-field :label="label">
		<ul class="list" v-if="value && value.length">
			<li class="list-item" v-for="file in value">
				<span v-if="resourceArchived">{{file.name}}</span>
				<a :href="getFileLink(file)" class="component" download target="_blank" v-else>{{file.name}}</a>
			</li>
		</ul>
	</b-field>
</template>

<script>
	import {getApiResourceFileLink} from "@plugins/api";

	export default {
		name: "ResAttrDisplayFiles",
		props: {
			label: String,
			value: Array,
			resource: Object
		},
		computed: {
			resourceArchived() {
				return !!this.resource.archived;
			}
		},
		methods: {
			getFileLink(fileData) {
				return getApiResourceFileLink(fileData, this.resource.__id);
			}
		}
	};
</script>

<style lang="scss">

</style>