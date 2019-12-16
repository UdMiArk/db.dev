<template>
	<b-field
			:label="label"
			:message="errors"
			:type="errors && 'is-danger'"
	>
		<div class="file">
			<b-upload :disabled="disabled" multiple v-model="model">
				<a :disabled="disabled" class="button is-primary">
					<b-icon icon="upload"/>
					<span>Загрузить</span>
				</a>
				<ul class="file-name" v-if="model && model.length">
					<li v-for="file in model">{{file.name}}</li>
				</ul>
			</b-upload>
		</div>
	</b-field>
</template>

<script>
	export default {
		name: "ResAttrFieldFiles",
		props: {
			label: String,
			value: Array,
			errors: [Array, String],
			disabled: Boolean
		},
		computed: {
			model: {
				get() {
					return this.value;
				},
				set(val) {
					this.$emit("input", val);
				}
			}
		}
	};
</script>

<style lang="scss">

</style>