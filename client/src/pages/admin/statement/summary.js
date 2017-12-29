import { myTools } from '../index.js'
import MyDatePicker from '../../../components/MyDatePicker.vue'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyDatePicker,
    MyToastr,
  },
  data: {
    httpClient: myTools.axiosInstance,
    msgResolver: myTools.msgResolver,
    dateFormat: 'YYYY-MM-DD',
    formData: {
      date: moment().format('YYYY-MM-DD'),
    },

    loading: true,
    summaryDataApi: '/admin/api/statement/summary',
    summaryExcelDataApi: '/admin/api/statement/summary/excel',
    summaryData: {},

    realTimeDataApi: '/admin/api/statement/real-time',
    realTimeData: {},
  },

  methods: {
    getStatementSummary () {
      let _self = this
      let toastr = this.$refs.toastr

      this.httpClient.get(this.summaryDataApi, {
        params: this.formData,
      })
        .then(function (res) {
          _self.msgResolver(res, toastr)
          _self.summaryData = res.data
        })
    },
  },

  created: function () {
    let _self = this

    //获取实时数据
    this.httpClient.get(this.realTimeDataApi)
      .then(function (res) {
        _self.realTimeData = res.data
      })
  },

  mounted: function () {
    let _self = this
    let toastr = this.$refs.toastr

    //获取总览数据
    this.httpClient.get(this.summaryDataApi, {
      params: this.formData,
    })
      .then(function (res) {
        _self.msgResolver(res, toastr)
        _self.summaryData = res.data
        _self.loading = false
      })
  },
})