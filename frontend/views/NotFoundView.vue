<template>
	<BaseViewContainer>
		<div class="box">
			<h1 class="is-size-1">404 Страница не найдена</h1>
			<p>По этому адресу нет ничего кроме этой заглушки. Хотите перейти на
				<router-link :to="{name: 'home'}">главную</router-link>
				?
			</p>
		</div>
	</BaseViewContainer>
</template>

<script>
	import BaseViewContainer from "@components/BaseViewContainer";

	export default {
		name: "NotFoundView",
		components: {BaseViewContainer}
	};
</script>