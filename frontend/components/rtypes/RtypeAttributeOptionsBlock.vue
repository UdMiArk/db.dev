<template>
	<b-field
			:message="errors && errors.extensions"
			:type="errors && errors.extensions && 'is-danger'"
			label="Допустимые расширения файлов"
	>
		<span class="input is-multiline" v-if="readonly">
			<span v-if="extensions.length">
				<span class="tag mr-xs" v-for="ext in extensions">{{ext}}</span>
			</span>
		</span>
		<b-taginput :disabled="disabled" icon="label" placeholder="Введите расширение" v-else v-model="extensions"/>
	</b-field>
</template>

<script>
	export default {
		name: "RtypeAttributeOptionsBlock",
		props: {
			value: Object,
			errors: Array,
			disabled: Boolean,
			readonly: Boolean
		},
		computed: {
			extensions: {
				get() {
					return this.value?.extensions?.split(",") || [];
				},
				set(val) {
					const result = [];
					for (let tag of val) {
						tag = tag.split(".").map(x => x.trim()).filter(Boolean);
						if (tag.length) {
							result.push("." + tag.join("."));
						}
					}
					this.$emit("input", result.length ? Object.freeze({extensions: result.join(",")}) : null);
				}
			}
		}
	};
</script>
