/**
 * Created by liudian on 9/14/17.
 */

import Vue from 'vue'
import MyChart from '../components/LineChart.vue'

let app = new Vue({
    el: '#app',
    components: {
        MyChart,
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

    created: function () {
        let _self = this;

        //获取汇总数据
        axios.get(this.summaryDataApi)
            .then(function (response) {
                _self.summaryData = response.data
                _self.loading = false
            })
            .catch(function (err) {
                alert(err)
            })
    },

    mounted: function () {
        let _self = this

        //获取图表数据
        axios.get(this.chartDataApi)
            .then(function (response) {
                let data = response.data
                let chartSeriesData = {}

                for (let [key, value] of Object.entries(_self.keyMap)) {
                    chartSeriesData[key] = []
                    _self.chartOptions.legend.data.push(value)
                }

                for (let date in data) {
                    _self.chartOptions.xAxis.data.push(date)
                    for (let key in chartSeriesData) {
                        chartSeriesData[key].push(data[date][key])
                    }
                }

                for (let key in _self.keyMap) {
                    _self.chartOptions.series.push({
                        name: _self.keyMap[key],
                        type: 'line',
                        smooth: true,
                        data: chartSeriesData[key]
                    })
                }

                //_self.$root.eventHub.$emit('EChartMergeOptions', _self.chartOptions)
            })
            .catch(function (err) {
                alert(err)
            })
    }
});