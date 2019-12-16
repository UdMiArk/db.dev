<template>
	<b-field :label="label">
		<div class="input">
			<ul v-if="data && data.length">
				<li v-for="file in data">
					<a :href="getFileLink(file)" class="component" download target="_blank">{{file.name}}</a></li>
			</ul>
		</div>
	</b-field>
</template>

<script>
	import {getApiResourceFileLink} from "@plugins/api";

	export default {
		name: "ResAttrDisplayFiles",
		props: {
			label: String,
			value: {},
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