<template>
  <div class="fixed top-4 right-4 z-50">
    <TransitionGroup name="notification">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="mb-2 p-4 rounded-lg shadow-lg max-w-md transform transition-all duration-300"
        :class="{
          'bg-green-100 text-green-800 border border-green-400': notification.type === 'success',
          'bg-red-100 text-red-800 border border-red-400': notification.type === 'error',
          'bg-blue-100 text-blue-800 border border-blue-400': notification.type === 'info'
        }"
      >
        <div class="flex justify-between items-center">
          <p class="text-sm font-medium">{{ notification.message }}</p>
          <button
            @click="removeNotification(notification.id)"
            class="ml-4 text-gray-500 hover:text-gray-700"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useNotificationStore } from '@/stores/notification'
import { storeToRefs } from 'pinia'

const store = useNotificationStore()
const { notifications } = storeToRefs(store)
const { removeNotification } = store
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style> 