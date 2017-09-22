/**
 * Created by liudian on 9/14/17.
 */

import Vue from 'vue'
import _ from 'lodash'
import MainHeader from '../components/MainHeader.vue'
import ChangePass from '../components/ChangePass.vue'
import SideBar from '../components/SideBar.vue'
import MyChart from '../components/LineChart.vue'
import MainFooter from '../components/MainFooter.vue'
import axios from 'axios'

new Vue({
  el: '#app',
  components: {
    MainHeader,
    ChangePass,
    SideBar,
    MyChart,
    MainFooter,
  },
  data: {
    eventHub: new Vue(),

    loading: true,
    summaryDataApi: '/admin/api/home/summary',
    summaryData: {},

    keyMap: {       //接口返回数据的key名与展示的名字的映射关系
      card_purchased: '房卡购买量',
      coin_purchased: '金币购买量',
      card_consumed: '房卡消耗量',
      coin_consumed: '金币消耗量',
    },
    chartDataApi: '/admin/api/statement/hourly-chart',
    chartOptions: {
      title: {
        text: '每小时流水',
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
      for (let [key, value] of Object.entries(this.keyMap)) {
        chartSeriesData[key] = []
        this.chartOptions.legend.data.push(value)
      }

      //生成x轴的数据，填充series每个数据列的数据
      this.chartOptions.xAxis.data = Object.keys(data)
      for (let key in chartSeriesData) {
        chartSeriesData[key] = _.map(Object.values(data), key)
      }

      //格式化series选项
      for (let key in this.keyMap) {
        this.chartOptions.series.push({
          name: this.keyMap[key],
          type: 'line',
          smooth: true,
          data: chartSeriesData[key]
        })
      }
    }
  },

  created: function () {
    let _self = this

    //获取汇总数据
    axios.get(this.summaryDataApi)
      .then(function (response) {
        _self.summaryData = response.data
        _self.loading = false
      })
  },

  mounted: function () {
    let _self = this

    //获取图表数据
    axios.get(this.chartDataApi)
      .then(function (response) {
        let data = response.data

        _self.prepareChartOptions(data)

        //触发事件，更新图表数据
        window.setTimeout(() => {
          _self.$root.eventHub.$emit('EChartMergeOptions', _self.chartOptions)
        }, 1000)
      })
  }
})