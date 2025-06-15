<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import * as echarts from 'echarts'
import { useEmployeeStore } from '@/stores/employee'

const store = useEmployeeStore()
const chartRef = ref<HTMLElement | null>(null)
let chart: echarts.ECharts | null = null

const initChart = () => {
  if (chartRef.value) {
    chart = echarts.init(chartRef.value)
  }
}

const updateChart = () => {
  if (!chart || !store.statistics) return

  const option = {
    title: {
      text: 'Employee Statistics'
    },
    tooltip: {
      trigger: 'axis'
    },
    legend: {
      data: ['Total Employees', 'Active Employees', 'Inactive Employees', 'On Leave']
    },
    xAxis: {
      type: 'category',
      data: store.statistics.departments.map((dept: any) => dept.name)
    },
    yAxis: {
      type: 'value'
    },
    series: [
      {
        name: 'Total Employees',
        type: 'bar',
        data: store.statistics.departments.map((dept: any) => dept.total_employees)
      },
      {
        name: 'Active Employees',
        type: 'bar',
        data: store.statistics.departments.map((dept: any) => dept.active_employees)
      },
      {
        name: 'Inactive Employees',
        type: 'bar',
        data: store.statistics.departments.map((dept: any) => dept.inactive_employees)
      },
      {
        name: 'On Leave',
        type: 'bar',
        data: store.statistics.departments.map((dept: any) => dept.on_leave_employees)
      }
    ]
  }

  chart.setOption(option)
}

// Watch for statistics changes
watch(() => store.statistics, () => {
  updateChart()
}, { deep: true })

onMounted(async () => {
  initChart()
  await store.fetchStatistics()
})
</script>

<template>
  <div class="w-full h-[400px] bg-white rounded-lg shadow-lg p-4">
    <div ref="chartRef" class="w-full h-full"></div>
  </div>
</template> 