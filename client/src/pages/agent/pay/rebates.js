import { myTools } from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyDatePicker,
    MyToastr,

  },
  data: {
    eventHub: new Vue(),
    httpClient: myTools.axiosInstance,
    dateFormat: 'YYYY-MM-DD',
    formData: {
      date: moment().format('YYYY-MM-DD'),
      end_date: moment().format('YYYY-MM-DD'),
    },
    statisticsData:{},

    statisticsApi:'/agent/api/rebates/statistics',
    tableUrl: '/agent/api/rebates',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
      },
      {
        name: 'total_amount',
        title: '当月充值金额',
      },
      {
        name: 'children.name',
        title: '提供的代理商',
      },
      {
        name: 'children.group.name',
        title: '代理级别',
      },
      {
        name: 'rebate_at',
        title: '年月份',
      },
      {
        name: 'rebate_price',
        title: '返利金额',
      },
      {
        name: 'rule.rate',
        title: '返利比例（单位 %）',
      },
    ],
  },
  methods: {
    getStatement () {
      let date = this.formData.date
      // tableUrl 改变 前端 vue-table 会自动刷新
      this.tableUrl = '/agent/api/rebates?date=' + date + '&end_time=' + this.formData.end_date
    },
  },
  mounted: function () {
    let _self = this

    //获取实时数据
    this.httpClient.get(this.statisticsApi)
      .then(function (res) {
        _self.statisticsData = res.data
      })
  },
})