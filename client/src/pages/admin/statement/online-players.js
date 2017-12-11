import '../index.js'
import MyChart from '../../../components/MyLineChart.vue'

new Vue({
  el: '#app',
  components: {
    MyChart,
  },
  data: {
    eventHub: new Vue(),

    keyMap: {
      online_count: '在线玩家',
    },
    chartDataApi: '/admin/api/statement/online-players',
    chartOptions: {
      title: {
        text: '',
      },
      legend: {
        data: [],
      },
      xAxis: {
        data: [],
      },
      series: [],
    },
  },

  methods: {
    prepareChartOptions (data) {
      let chartSeriesData = {}

      //生成legend数据，series data的空数组
      for (let [key, value] of _.entries(this.keyMap)) {
        chartSeriesData[key] = []
        this.chartOptions.legend.data.push(value)
      }

      //生成x轴的数据，填充series每个数据列的数据
      this.chartOptions.xAxis.data = _.keys(data)
      for (let key in chartSeriesData) {
        chartSeriesData[key] = _.map(Object.values(data), key)
      }

      //格式化series选项
      for (let key in this.keyMap) {
        this.chartOptions.series.push({
          name: this.keyMap[key],
          type: 'line',
          smooth: true,
          data: chartSeriesData[key],
        })
      }
    },
  },

  created: function () {
    let _self = this

    //获取图表数据
    axios.get(this.chartDataApi + '?date=2017-12-08')
      .then(function (response) {
        let data = response.data

        _self.prepareChartOptions(data)

        //触发事件，更新图表数据(不做定时器，x轴数据显示不出来)
        window.setTimeout(() => {
          _self.$root.eventHub.$emit('MyLineChart:mergeOptions', _self.chartOptions)
        }, 1000)
      })
  },
})