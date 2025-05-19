<script setup lang="ts">
import MainJumbotron from '@frontend/components/MainJumbotron.vue'
import MainStatistic from './MainStatistic.vue'

</script>

<template>
  <MainJumbotron
    title="Добро пожаловать!"
    lead="Сервис контроля лендингов и их аналитик"
  >
    <MainStatistic v-if="isAuthorized" />
  </MainJumbotron>
</template>
