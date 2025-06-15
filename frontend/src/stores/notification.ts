import { defineStore } from 'pinia'

interface Notification {
  id: number
  message: string
  type: 'success' | 'error' | 'info'
  timeout?: number
}

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [] as Notification[]
  }),

  actions: {
    addNotification(message: string, type: 'success' | 'error' | 'info' = 'success', timeout: number = 3000) {
      const id = Date.now()
      this.notifications.push({ id, message, type, timeout })
      
      if (timeout) {
        setTimeout(() => {
          this.removeNotification(id)
        }, timeout)
      }
    },

    removeNotification(id: number) {
      this.notifications = this.notifications.filter(notification => notification.id !== id)
    }
  }
}) 