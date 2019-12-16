<template>
	<ul class="app-resourcetypeselector">
		<li class="app-resourcetypeselector__item-wrapper" v-for="item in items">
			<button :dataId.prop="item.__id" :title="item.description" @click="handleItemClick" class="app-resourcetypeselector__item" type="button">
				<span class="app-resourcetypeselector__item-name">{{item.name}}</span>
			</button>
		</li>
	</ul>
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
		methods: {
			handleItemClick(ev) {
				this.$emit("select", ev.target.closest("button").dataId);
			}
		}
	};
</script>

<style lang="scss">
	.app-resourcetypeselector {
		$padding: 0.5rem;
		$width: 8rem;

		display: block;

		list-style: none;
		margin: -$padding;

		&__item {
			display: block;

			margin: 0;
			padding: 0.5rem;
			height: $width;

			cursor: pointer;
			width: 100%;

			&-wrapper {
				display: inline-block;
				width: $width + $padding * 2;
				padding: $padding;
				margin: 0;

				vertical-align: top;
			}

			&-title {

			}
		}
	}
</style>