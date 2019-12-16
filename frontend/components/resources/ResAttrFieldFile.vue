<template>
	<b-field
			:label="label"
			:message="errors"
			:type="errors && 'is-danger'"
	>
		<div class="file">
			<b-upload :disabled="disabled" v-model="model">
				<a :disabled="disabled" class="button is-primary">
					<b-icon icon="upload"/>
					<span>Загрузить</span>
				</a>
				<span class="file-name" v-if="model">{{ model.name }}</span>
			</b-upload>
		</div>
	</b-field>
</template>

<script>
	export default {
		name: "ResAttrFieldFile",
		props: {
			label: String,
			value: {},
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