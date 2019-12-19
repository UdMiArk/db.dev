<template>
	<BaseForm #default="{props}" :data="defaultValues" :locked="processing" :watchers="formWatchers" @submit="handleSubmit" class="app-form card" ref="display">
		<header class="card-header">
			<span class="card-header-title">Добавление ресурса</span>
		</header>
		<section class="card-content">
			<div class="columns">
				<div class="column is-one-quarter-desktop is-half">
					<b-field
							:message="errors && errors.market_id"
							:type="errors && errors.market_id && 'is-danger'"
							label="Рынок"
					>
						<b-select :disabled="processing" :readonly="props.type_id != null" expanded name="market_id" required v-model="props.market_id">
							<option :disabled="!productsByMarket[market.__id]"
									:key="market.__id"
									:title="productsByMarket[market.__id] ? undefined : 'Без доступных объектов'"
									:value="market.__id"
									v-for="market in creationData.markets"
							>{{market.name}}
							</option>
						</b-select>
					</b-field>
				</div>
				<div class="column is-three-quarters-desktop is-half">
					<b-field
							:message="errors && errors.product_id_ext"
							:type="errors && errors.product_id_ext && 'is-danger'"
							label="Объект продвижения"
					>
						<AutocompleteSelectField
								:disabled="processing || props.market_id == null"
								:items="productsByMarket[props.market_id] || []"
								:readonly="props.type_id != null"
								name="product_id_ext"
								outputField="name"
								required
								v-model="props.product_id_ext"
								valueField="id"
						/>
					</b-field>
				</div>
			</div>
			<div class="box has-text-centered" v-if="props.product_id_ext == null">
				Для продолжения выберите рынок и объект продвижения
			</div>
			<ResourceTypeSelector :items="creationData.resourceTypes" @select="props.type_id = $event" v-else-if="props.type_id == null"/>
			<div v-else>
				<div class="columns">
					<div class="column is-one-quarter-desktop is-half">
						<b-field label="Тип ресурса">
							<b-field>
								<b-input :value="getResourceType(props.type_id).name" readonly/>
								<p class="control">
									<b-button @click="props.type_id = null" icon-left="backspace" title="Выбрать другой тип"/>
								</p>
							</b-field>
						</b-field>
					</div>
					<div class="column is-three-quarters-desktop is-half">
						<b-field
								:message="errors && errors.name"
								:type="errors && errors.name && 'is-danger'"
								label="Имя ресурса"
						>
							<b-input :disabled="processing" name="name" required v-model="props.name"/>
						</b-field>
					</div>
				</div>
				<ResourceDataFormBlock :disabled="processing" :errors="dataErrors" :resourceType="getResourceType(props.type_id)" v-model="props.data"/>
				<b-field :message="errors && errors.comment"
						 :type="errors && errors.comment && 'is-danger'"
						 label="Комментарий"
						 v-if="props.type_id != null"
				>
					<b-input :disabled="processing" name="comment" type="textarea" v-model="props.comment"/>
				</b-field>
			</div>
		</section>
		<footer class="card-content has-text-right has-background-light" v-if="props.type_id">
			<b-button :loading="processing" native-type="submit" type="is-primary">Отправить</b-button>
		</footer>
	</BaseForm>
</template>

<script>
	import BaseForm from "@components/form/BaseForm";
	import AutocompleteSelectField from "@components/form/AutocompleteSelectField";
	import ResourceTypeSelector from "@components/resources/ResourceTypeSelector";
	import ResourceDataFormBlock from "@components/resources/ResourceDataFormBlock";
	import {jsonToFormData} from "@plugins/api";
	import {deepFreeze} from "@plugins/object";

	export default {
		name: "ResourceCreateForm",
		components: {ResourceDataFormBlock, ResourceTypeSelector, AutocompleteSelectField, BaseForm},
		props: {
			creationData: {
				type: Object,
				required: true
			}
		},
		data() {
			return {
				errors: undefined,
				processing: false
			};
		},
		computed: {
			defaultValues() {
				return Object.freeze({
					market_id: null,
					product_id_ext: null,
					type_id: null,
					name: "",
					comment: "",
					data: Object.freeze({})
				});
			},
			formWatchers() {
				return Object.freeze({
					market_id(newValue, oldValue) {
						this.set("product_id_ext", null);
					},
					type_id(newValue, oldValue) {
						if (newValue == null) {
							this.set("data", Object.freeze({}));
						}
					}
				});
			},
			productsByMarket() {
				const
					markets = this.creationData.markets,
					products = this.creationData.products,
					marketsMap = {},
					result = {};

				for (const {__id, id_ext} of markets) {
					marketsMap[id_ext] = __id;
					result[__id] = [];
				}

				for (const product of products) {
					const market = marketsMap[product.market];
					if (market == null) {
						console.error(`Unknown market id '${market}' of product ${product.id}`);
					} else {
						result[market].push(product);
					}
				}

				for (const {__id} of markets) {
					if (result[__id].length) {
						Object.freeze(result[__id]);
					} else {
						delete result[__id];
					}
				}

				return Object.freeze(result);
			},
			dataErrors() {
				const errors = this.errors;
				if (!errors) {
					return undefined;
				}
				const result = {};
				if (errors["data"]) {
					result[""] = errors["data"];
				}
				for (const key of Object.keys(errors)) {
					if (key.startsWith("data.")) {
						result[key.substr(5)] = errors[key];
					}
				}
				return Object.keys(result).length ? Object.freeze(result) : undefined;
			}
		},
		methods: {
			handleSubmit({changes}) {
				const processDisplay = this.$buefy.loading.open({container: this.$refs.display?.$el || this.$el});
				this.processing = true;
				this.errors = undefined;
				this.$apiPostJ("resources/create", jsonToFormData(changes))
					.then(({data}) => {
						if (data.success) {
							this.$emit("success", deepFreeze(data));
						} else if (data.errors) {
							this.errors = deepFreeze(data.errors);
						} else {
							this.$handleErrorWithBuefy(data.error || "Не удалось прочитать ответ сервера");
						}
					})
					.catch(this.$handleErrorWithBuefy)
					.finally(() => (this.processing = false, processDisplay.close()));
			},
			getResourceType(typeId) {
				return this.creationData.resourceTypes.find(x => x.__id === typeId);
			}
		}
	};
</script>

<style lang="scss">

</style>