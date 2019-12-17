<template>
	<table class="table is-fullwidth app-roletoggler">
		<tbody>
		<tr class="app-roletoggler__row app-roletoggler__row--empty" v-if="!structure.length">
			<td class="is-italic is-centered">
				В приложении не определено ни одной назначабельной роли
			</td>
		</tr>
		<template v-else v-for="item in structure">
			<template v-if="item.items">
				<tr :key="item.id" class="app-roletoggler__row app-roletoggler__row-group">
					<th class="app-roletoggler__col" colspan="3">{{item.name}}</th>
				</tr>
				<tr :key="subitem.id" class="app-roletoggler__row app-roletoggler__row-subitem" v-for="subitem in item.items">
					<th class="app-roletoggler__col app-roletoggler__col-name app-roletoggler__col-name--subitem">{{subitem.name}}</th>
					<td class="app-roletoggler__col app-roletoggler__col-desc">{{subitem.description}}</td>
					<td class="app-roletoggler__col app-roletoggler__col-switch">
						<b-switch :value="valueMap[subitem.id] || false" @input="handleToggle(subitem.id, $event)"/>
					</td>
				</tr>
			</template>
			<tr :key="item.id" class="app-roletoggler__row app-roletoggler__row-item" v-else>
				<th class="app-roletoggler__col app-roletoggler__col-name">{{item.name}}</th>
				<td class="app-roletoggler__col app-roletoggler__col-desc">{{item.description}}</td>
				<td class="app-roletoggler__col app-roletoggler__col-switch">
					<b-switch :value="valueMap[item.id] || false" @input="handleToggle(item.id, $event)"/>
				</td>
			</tr>
		</template>
		</tbody>
	</table>
</template>

<script>
	export default {
		name: "RoleToggler",
		props: {
			roles: Array,
			value: Array
		},
		computed: {
			valueMap() {
				return Object.freeze(this.value ? this.value.reduce((r, x) => (r[x] = true, r), {}) : {});
			},
			structure() {
				const result = [],
					groups = {},
					roles = this.roles;

				for (const role of roles) {
					if (role.group) {
						if (!groups[role.group]) {
							groups[role.group] = [];
						}
						groups[role.group].push(role);
					} else {
						result.push(role);
					}
				}

				const groupNames = Object.keys(groups);
				groupNames.sort();

				for (const group of groupNames) {
					result.push(Object.freeze({
						id: "g_" + group,
						name: group,
						items: Object.freeze(groups[group])
					}));
				}

				return Object.freeze(result);
			}
		},
		methods: {
			handleToggle(role, state) {
				this.$emit("toggle", {role, state});
			}
		}
	};
</script>

<style lang="scss">
	@import "~@assets/_bulma_vars.scss";

	.app-roletoggler {
		&__col {
			&-switch {
				width: 76px;
			}

			&-name {
				&--subitem {
					padding-left: 2rem !important;
					color: $grey-dark !important;
				}
			}
		}

		&__row {
			&-item {

			}

			&-subitem {

			}

			&-group {
				background-color: #eeeeff;
			}
		}
	}
</style>