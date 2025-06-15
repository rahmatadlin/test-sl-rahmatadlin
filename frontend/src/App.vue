<script setup lang="ts">
import { RouterView } from 'vue-router'
import Sidebar from './components/Sidebar.vue'
import Notification from '@/components/Notification.vue'
</script>

<template>
  <div class="flex min-h-screen w-full m-0">
    <div class="flex-shrink-0">
      <Sidebar />
    </div>
    <main class="flex-1 overflow-x-auto">
      <RouterView />
    </main>
    <Notification />
  </div>
</template>

<style>
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';

body {
  margin: 0;
  padding: 0;
}
</style>
