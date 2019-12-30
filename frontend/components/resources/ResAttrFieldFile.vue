<template>
	<b-field
			:label="label"
			:message="errors"
			:type="errors && 'is-danger'"
	>
		<div class="file">
			<b-upload :accept="extensions" :disabled="disabled" :required="required" v-model="model">
				<a :disabled="disabled" class="button is-primary">
					<b-icon icon="upload"/>
					<span>Выбрать файл</span>
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
			options: Object,
			errors: [Array, String],
			disabled: Boolean,
			required: Boolean
		},
		computed: {
			model: {
				get() {
					return this.value;
				},
				set(val) {
					this.$emit("input", val);
				}
			},
			extensions() {
				return this.options?.extensions;
			}
		}
	};
</script>
