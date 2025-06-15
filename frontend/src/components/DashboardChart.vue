<script setup lang="ts">
import { onMounted, ref } from 'vue'
import * as echarts from 'echarts'

const chartRef = ref<HTMLElement | null>(null)
let chart: echarts.ECharts | null = null

onMounted(() => {
  if (chartRef.value) {
    chart = echarts.init(chartRef.value)
    
    const option = {
      title: {
        text: 'Sample Dashboard'
      },
      tooltip: {
        trigger: 'axis'
      },
      xAxis: {
        type: 'category',
        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
      },
      yAxis: {
        type: 'value'
      },
      series: [
        {
          name: 'Sample Data',
          type: 'line',
          data: [150, 230, 224, 218, 135, 147, 260]
        }
      ]
    }

    chart.setOption(option)
  }
})
</script>

<template>
  <div class="w-full h-[400px] bg-white rounded-lg shadow-lg p-4">
    <div ref="chartRef" class="w-full h-full"></div>
  </div>
</template> 