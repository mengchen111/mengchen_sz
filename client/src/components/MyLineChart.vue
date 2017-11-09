<template>
    <chart ref="echart"
           :options="options"
           :auto-resize="true">
    </chart>
</template>

<script>
  import ECharts from 'vue-echarts'

  export default {
    components: {
      'chart': ECharts,
    },
    props: {
      chartOptions: {
        type: Object,
        required: true,
      },
    },
    data: function () {
      return {
        eventPrefix: 'MyLineChart',
        options: {
          title: {
            text: this.chartOptions.title.text,
          },
          tooltip: {
            trigger: 'axis',
          },
          legend: {
            data: this.chartOptions.legend.data,
          },
          toolbox: {
            show: true,
            feature: {
              saveAsImage: {show: true},
            },
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: this.chartOptions.xAxis.data,
          },
          yAxis: {
            type: 'value',
          },
          series: this.chartOptions.series,
        },
      }
    },

    methods: {
      onEChartMergeOptions (options) {
        this.$refs.echart.mergeOptions(options)
      },
    },

    mounted: function () {
      this.$root.eventHub.$on(`${this.eventPrefix}:mergeOptions`, this.onEChartMergeOptions)
    },
  }
</script>



