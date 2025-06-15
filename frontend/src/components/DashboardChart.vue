<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import * as echarts from 'echarts'
import { useEmployeeStore } from '@/stores/employee'

const store = useEmployeeStore()
const chartRef = ref<HTMLElement | null>(null)
let chart: echarts.ECharts | null = null

const initChart = () => {
  if (chartRef.value) {
    console.log('Initializing chart with container:', chartRef.value)
    chart = echarts.init(chartRef.value)
    // Add resize listener
    window.addEventListener('resize', () => {
      chart?.resize()
    })
  }
}

const updateChart = () => {
  console.log('Updating chart with statistics:', store.statistics)
  if (!chart) {
    console.error('Chart instance is null')
    return
  }
  if (!store.statistics) {
    console.error('Statistics data is null')
    return
  }
  if (!store.statistics.by_department) {
    console.error('Department data is missing')
    return
  }

  const option = {
    title: {
      text: 'Statistics by Department'
    },
    tooltip: {
      trigger: 'axis'
    },
    legend: {
      data: ['Total Employees']
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '15%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: store.statistics.by_department.map((dept: any) => dept.departemen),
      axisLabel: {
        interval: 0,
        rotate: 45,
        margin: 20
      }
    },
    yAxis: {
      type: 'value',
      minInterval: 1,
      axisLabel: {
        formatter: (value: number) => Math.round(value).toString()
      }
    },
    series: [
      {
        name: 'Total Employees',
        type: 'bar',
        data: store.statistics.by_department.map((dept: any) => dept.count)
      }
    ]
  }

  try {
    chart.setOption(option)
    console.log('Chart updated successfully')
  } catch (error) {
    console.error('Error updating chart:', error)
  }
}

// Watch for statistics changes
watch(() => store.statistics, (newStats) => {
  console.log('Statistics changed:', newStats)
  updateChart()
}, { deep: true })

onMounted(async () => {
  console.log('Component mounted')
  initChart()
  try {
    await store.fetchStatistics()
    console.log('Statistics fetched successfully')
  } catch (error) {
    console.error('Error fetching statistics:', error)
  }
})
</script>

<template>
  <div class="w-full h-[400px] bg-white rounded-lg shadow-lg p-4">
    <div ref="chartRef" class="w-full h-full min-w-[800px]"></div>
  </div>
</template> 