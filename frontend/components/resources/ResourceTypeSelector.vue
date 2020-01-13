<template>
	<div :class="$style.host">
		<div :class="$style.group" :key="group.name" v-for="group in groups">
			<h5 :class="$style.group_title">{{group.name}}</h5>
			<ul :class="$style.list">
				<li :class="$style.list_item_wrapper" :key="item.__id" v-for="item in group.items">
					<button :class="$style.list_item" :dataId.prop="item.__id" :title="item.description" @click="handleItemClick" type="button">
						<span :class="$style.list_item_name">{{item.name}}</span>
					</button>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	export default {
		name: "ResourceTypeSelector",
		props: {
			items: {
				type: Array,
				required: true
			}
		},
		computed: {
			groups() {
				const resultMap = {};
				for (const item of this.items) {
					if (!resultMap[item.group_name]) {
						resultMap[item.group_name] = [];
					}
					resultMap[item.group_name].push(item);
				}
				return Object.freeze(Object.keys(resultMap).sort().map(x => Object.freeze({
					name: x,
					items: Object.freeze(resultMap[x])
				})));
			}
		},
		methods: {
			handleItemClick(ev) {
				this.$emit("select", ev.target.closest("button").dataId);
			}
		}
	};
</script>

<style lang="scss" module>
	$padding: 0.5rem;
	$width: 8rem;

	.host {

	}

	.group {
		margin-bottom: 1rem;
	}

	.group_title {
		font-weight: bold;
		font-size: 1rem;
		margin-bottom: 0.2rem;
	}

	.list {
		display: block;

		list-style: none;
		margin: -$padding;
	}

	.list_item_wrapper {
		display: inline-block;
		width: $width + $padding * 2;
		padding: $padding;
		margin: 0;

		vertical-align: top;
	}

	.list_item {
		display: block;

		margin: 0;
		padding: 0.5rem;
		height: $width / 2;

		cursor: pointer;
		width: 100%;
	}

	.list_item_name {
		font-size: 1rem;
	}
</style>