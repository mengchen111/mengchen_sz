import '../index.js'
import MyChart from '../../../components/MyLineChart.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'

new Vue({
  el: '#app',
  components: {
    MyChart,
    MyDatePicker,
  },
  data: {
    eventHub: new Vue(),
    dateFormat: 'YYYY-MM-DD',
    date: moment().format('YYYY-MM-DD'),

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

  watch: {
    date: function (val) {
      this.refreshGraph(val)
    },
  },

  methods: {
    prepareChartOptions (data) {
      let chartSeriesData = {}
      let options = _.cloneDeep(this.chartOptions)

      //生成legend数据，series data的空数组
      for (let [key, value] of _.entries(this.keyMap)) {
        chartSeriesData[key] = []
        options.legend.data.push(value)
      }

      //生成x轴的数据，填充series每个数据列的数据
      options.xAxis.data = _.keys(data)
      for (let key in chartSeriesData) {
        chartSeriesData[key] = _.map(Object.values(data), key)
      }

      //格式化series选项
      for (let key in this.keyMap) {
        options.series.push({
          name: this.keyMap[key],
          type: 'line',
          smooth: true,
          data: chartSeriesData[key],
        })
      }

      return options
    },

    //刷新图表
    refreshGraph (date) {
      let _self = this

      //获取图表数据
      axios.get(this.chartDataApi + '?date=' + date)
        .then(function (response) {
          let options = _self.prepareChartOptions(response.data)

          //触发事件，更新图表数据(不做定时器，x轴数据显示不出来)
          window.setTimeout(() => {
            _self.$root.eventHub.$emit('MyLineChart:mergeOptions', options)
          }, 100)
        })
    },
  },

  created () {
    this.refreshGraph(this.date)
  },
})