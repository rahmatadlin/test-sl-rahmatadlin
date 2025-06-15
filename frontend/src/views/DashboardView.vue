<template>
  <div class="p-6 w-full">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    <div class="w-full">
      <DashboardChart />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useEmployeeStore } from '@/stores/employee'
import DashboardChart from '@/components/DashboardChart.vue'

const store = useEmployeeStore()

onMounted(async () => {
  try {
    await store.fetchStatistics()
  } catch (error) {
    console.error('Error fetching statistics:', error)
  }
})
</script> 