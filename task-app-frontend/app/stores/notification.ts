import { defineStore } from "pinia";
import { ref } from "vue";


export interface Notification {
  id: string
  message: string
  color: string
}

export const useNotificationStore= defineStore('notifications' , () => {
  // State
  const notifications = ref<Notification[]>([])

  // Actions
  const add = (message: string, color:string = 'success') => {
    const id = crypto.randomUUID()
    notifications.value.push({id: id, message, color})

    // Auto-remove after 2 seconds
    setTimeout(() => remove(id), 3000);
  }

  const remove = (id: string) => {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  return { notifications, add, remove}
})