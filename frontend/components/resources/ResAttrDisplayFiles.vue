<template>
	<b-field :label="label">
		<ul class="list" v-if="value && value.length">
			<li class="list-item" v-for="file in value">
				<a :href="getFileLink(file)" class="component" download target="_blank">{{file.name}}</a>
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
		methods: {
			getFileLink(fileData) {
				return getApiResourceFileLink(fileData, this.resource.__id);
			}
		}
	};
</script>

<style lang="scss">

</style>