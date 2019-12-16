<template>
	<transition mode="out-in" name="fade">
		<router-view/>
	</transition>
</template>

<script>
	export default {
		name: "AppContent"
	};
</script>

<style lang="scss">

</style>