<template>
	<div class="field">
		<label class="label">
			{{label + (required ? " *" : "")}}
			<b-tooltip :label="description" position="is-right" size="is-small" style="cursor: help" v-if="description">
				<b-icon icon="help-circle" size="is-small"/>
			</b-tooltip>
		</label>
		<div class="file" v-if="!model">
			<b-upload :accept="extensions" :disabled="disabled" v-model="model">
				<a :disabled="disabled" class="button is-primary">
					<b-icon icon="upload"/>
					<span>Выбрать файл</span>
				</a>
			</b-upload>
		</div>
		<div v-else>
			<table class="table is-fullwidth">
				<tbody>
				<tr>
					<td>{{model.name}}</td>
					<td style="width: 100px; text-align: right">
						<span :class="{'has-text-danger': model.size > maxSize}" :title="model.size > maxSize ? $msgTooLarge : undefined">{{model.size | fileSize}}</span>
					</td>
					<td style="width: 20px; text-align: center">
						<b-button :disabled="disabled" @click="model = null" icon-left="delete" title="Исключить" type="is-warning"/>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		<p class="help is-danger" v-if="errors && errors.length">
			<template v-for="(err, idx) in errorsArray">
				<br v-if="idx"/>
				{{err}}
			</template>
		</p>
	</div>
</template>

<script>
	import {getReadableSize} from "@plugins/index";

	export default {
		name: "ResAttrFieldFile",
		props: {
			label: String,
			description: String,
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
			errorsArray() {
				const errors = this.errors;
				if (typeof errors === "string") {
					return Object.freeze([errors]);
				}
				return errors;
			},
			maxSize() {
				return 2 * 1024 ** 3;
			},
			extensions() {
				return this.options?.extensions;
			}
		},
		created() {
			this.$msgTooLarge = "Размер файла превышает допустимый: " + getReadableSize(this.maxSize);
		}
	};
</script>
