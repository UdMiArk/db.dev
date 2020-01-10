<template>
	<div class="field">
		<label class="label">
			{{label + (required ? " *" : "")}}
			<b-tooltip :label="description" position="is-right" size="is-small" style="cursor: help" v-if="description">
				<b-icon icon="help-circle" size="is-small"/>
			</b-tooltip>
		</label>
		<div class="file">
			<b-upload :accept="extensions" :disabled="disabled" multiple v-model="model">
				<a :disabled="disabled" class="button is-primary">
					<b-icon icon="upload"/>
					<span>Добавить файлы</span>
				</a>
			</b-upload>
		</div>
		<p class="help is-danger" v-if="errors && errors.length">
			<template v-for="(err, idx) in errorsArray">
				<br v-if="idx"/>
				{{err}}
			</template>
		</p>
		<div>
			<table class="table is-fullwidth" v-if="model && model.length">
				<tbody>
				<tr :key="idx" v-for="(file, idx) in model">
					<td>{{file.name}}</td>
					<td style="width: 100px; text-align: right">
						<span :class="{'has-text-danger': file.size > maxSize}" :title="file.size > maxSize ? $msgTooLarge : undefined">{{file.size | fileSize}}</span>
					</td>
					<td style="width: 20px; text-align: center">
						<b-button :disabled="disabled" @click="removeFile(idx)" icon-left="delete" title="Исключить" type="is-warning"/>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>

	import {getReadableSize} from "@plugins/index";

	export default {
		name: "ResAttrFieldFiles",
		props: {
			label: String,
			description: String,
			value: Array,
			errors: [Array, String],
			options: Object,
			disabled: Boolean,
			required: Boolean
		},
		computed: {
			maxSize() {
				return 2 * 1024 ** 3;
			},
			errorsArray() {
				const errors = this.errors;
				if (typeof errors === "string") {
					return Object.freeze([errors]);
				}
				return errors;
			},
			model: {
				get() {
					return this.value;
				},
				set(val) {
					this.$emit("input", val?.length ? val : null);
				}
			},
			extensions() {
				return this.options?.extensions;
			}
		},
		methods: {
			removeFile(idx) {
				const newValue = this.value.slice();
				newValue.splice(idx, 1);
				this.model = newValue;
			}
		},
		created() {
			this.$msgTooLarge = "Размер файла превышает допустимый: " + getReadableSize(this.maxSize);
		}
	};
</script>
