<template>
	<div :class="$style.host">
		<div class="columns">
			<div class="column is-three-fifths-desktop">
				<b-field
						:message="errors && errors.name"
						:type="errors && errors.name && 'is-danger'"
						label="Имя поля"
				>
					<b-input :disabled="disabled" :readonly="readonly" name="name" required v-model="props.name"/>
				</b-field>
			</div>
			<div class="column is-two-fifths-desktop">
				<div class="columns is-mobile is-multiline">
					<div class="column is-full-mobile is-three-fifths-tablet">
						<b-field
								:message="errors && errors.name"
								:type="errors && errors.name && 'is-danger'"
								label="Тип поля"
						>
							<span class="input" v-if="readonly">{{props.type | enumLabel($_ATTR_TYPES)}}</span>
							<b-select :disabled="disabled" expanded name="type" required v-else v-model="props.type">
								<option
										:key="type.value"
										:value="type.value"
										v-for="type in $_ATTR_TYPES"
								>{{type.label}}
								</option>
							</b-select>
						</b-field>
					</div>
					<div :class="['column is-three-fifths-mobile', removable ? 'is-one-fifth-tablet' : 'is-two-fifths-tablet']" style="margin-top: auto">
						<b-field label="Обязательное">
							<b-switch :disabled="disabled || readonly" size="is-large" v-model="props.required"/>
						</b-field>
					</div>
					<div class="column is-two-fifths-mobile is-one-fifth-tablet" style="margin-top: auto; text-align: right" v-if="removable">
						<b-button :title="'Удалить поле'" @click="$emit('remove')" icon-left="delete" type="is-warning"/>
					</div>
				</div>
			</div>
		</div>
		<component :disabled="disabled" :is="optionsComponent" :readonly="readonly" key="options" v-if="optionsComponent" v-model="props.options"/>
		<b-field
				:message="errors && errors.name"
				:type="errors && errors.name && 'is-danger'"
				key="description"
				label="Описание"
		>
			<b-input :disabled="disabled" :readonly="readonly" name="description" type="textarea" v-model="props.description"/>
		</b-field>
	</div>
</template>

<script>
	import {proxyHandler} from "@plugins/form";
	import {RESATTR_TYPES} from "@/data/resources";
	import RtypeAttributeOptionsBlock from "@components/rtypes/RtypeAttributeOptionsBlock";

	const FORM_PROPS = Object.freeze(["name", "type", "description", "options", "required"]);
	const TYPE_COMPONENTS = Object.freeze({
		[RESATTR_TYPES.FILE]: RtypeAttributeOptionsBlock,
		[RESATTR_TYPES.FILES]: RtypeAttributeOptionsBlock
	});


	const handleSetProp = function handleSetProp($changes, property, value) {
		const newChanges = $changes ? Object.assign({}, $changes) : {};
		newChanges[property] = value;
		this.$emit("input", Object.freeze(newChanges));
	};
	const handleDeleteProp = function handleDeleteProp($changes, property) {
		const newChanges = $changes ? Object.assign({}, $changes) : {};
		delete newChanges[property];
		this.$emit("input", Object.keys(newChanges).length ? Object.freeze(newChanges) : null);
	};

	export default {
		name: "RtypeAttributeEditBlock",
		props: {
			value: Object,
			changes: Object,
			errors: Array,
			readonly: Boolean,
			disabled: Boolean,
			removable: Boolean
		},
		computed: {
			props() {
				return new Proxy({
					$data: this.value,
					$props: FORM_PROPS,
					$changes: this.changes,
					$watchers: null,
					$set: this.$_handleSetProp,
					$delete: this.$_handleDeleteProp
				}, this.$_proxyHandler);
			},
			optionsComponent() {
				return TYPE_COMPONENTS[this.props.type];
			}
		},
		beforeCreate() {
			this.$_proxyHandler = proxyHandler;
			this.$_handleSetProp = handleSetProp.bind(this);
			this.$_handleDeleteProp = handleDeleteProp.bind(this);
			this.$_ATTR_TYPES = RESATTR_TYPES;
		}
	};
</script>

<!--suppress CssUnusedSymbol -->
<style lang="css" module>
	.host {
		padding-bottom: 0.75rem;
		border-bottom: 1px solid lightgray;
		margin-bottom: 0.75rem;
	}
</style>