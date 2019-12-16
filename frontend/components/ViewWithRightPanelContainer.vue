<template>
	<main class="app-container-right-panel columns is-fullheight">
		<aside class="column is-2 is-narrow-mobile is-fullheight section is-hidden-mobile">
			<slot name="sidebar"/>
		</aside>
		<div class="container column is-10 section">
			<slot/>
		</div>
	</main>
</template>

<script>
	export default {
		name: "ViewWithRightPanelContainer"
	};
</script>

<style lang="scss">

</style>