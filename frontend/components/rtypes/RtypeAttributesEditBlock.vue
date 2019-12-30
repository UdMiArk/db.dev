<template>
	<fieldset class="fieldset">
		<legend>Поля ресурса</legend>
		<div class="mb-sm" v-if="errors && errors.length">
			<ul class="list has-background-warning">
				<li class="list-item" v-for="err in errors">{{err}}</li>
			</ul>
		</div>
		<div>
			<RtypeAttributeEditBlock
					:changes="chAttr"
					:disabled="disabled"
					:errors="errors && errors[chAttr.__key]"
					:key="chAttr.__key"
					:readonly="readonly"
					:removable="!readonly"
					:value="value ? value[chAttr.__origIdx] : undefined"
					@input="handleAttrChange(chAttr, $event)"
					@remove="handleRemoveAttr(chAttr.__key)"
					v-for="chAttr in changedArr"
			/>
		</div>
		<div class="has-text-centered" v-if="!readonly">
			<b-button :disabled="disabled" @click="handleAddAttr">Добавить поле</b-button>
		</div>
	</fieldset>
</template>

<script>
	import {RESATTR_TYPES} from "@/data/resources";
	import RtypeAttributeEditBlock from "@components/rtypes/RtypeAttributeEditBlock";

	export default {
		name: "RtypeAttributesEditBlock",
		components: {RtypeAttributeEditBlock},
		props: {
			value: Array,
			changed: Array,
			errors: Array,
			disabled: Boolean,
			readonly: Boolean
		},
		computed: {
			changedArr() {
				return this.changed ? this.changed : Object.freeze(this.value ? this.value.map((x, i) => {
					return Object.freeze({
						__id: x.__id,
						__origIdx: i,
						__key: this.$_lastAttrKey++
					});
				}) : []);
			}
		},
		methods: {
			handleAddAttr() {
				const newChanged = this.changedArr.slice();
				newChanged.push(Object.freeze({
					__id: null,
					__key: "$" + (this.$_lastAttrKey++).toString(),
					name: "",
					type: RESATTR_TYPES.FILE,
					description: "",
					required: false,
					options: null
				}));
				this.$emit("input", Object.freeze(newChanged));
			},
			handleRemoveAttr(key) {
				const newChanged = this.changedArr.slice(),
					idx = newChanged.findIndex(x => x.__key === key);
				if (idx >= 0) {
					newChanged.splice(idx, 1);
					this.$emit("input", Object.freeze(newChanged));
				}
			},
			handleAttrChange($orig, $new) {
				const newChanged = this.changedArr.slice(),
					idx = newChanged.indexOf($orig);
				if (idx >= 0) {
					newChanged.splice(idx, 1, $new);
					this.$emit("input", Object.freeze(newChanged));
				}
			}
		},
		beforeCreate() {
			this.$_lastAttrKey = 0;
		}
	};
</script>

<style lang="scss">

</style>