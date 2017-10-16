import '../common.js'
import MyDatePicker from '../../../components/MyDatePicker.vue'

new Vue({
  el: '#app',
  components: {
    MyDatePicker,
  },
  data: {
    dateFormat: 'YYYY-MM-DD',
    formData: {
      date: moment(),

    },

    loading: true,
    summaryDataApi: '/admin/api/statement/summary',
    summaryData: {},

    realTimeDataApi: '/admin/api/statement/real-time',
    realTimeData: {},
  },

  methods: {
    getStatementSummary () {
      let _self = this

      axios.get(this.summaryDataApi, {
        params: this.formData,
      })
        .then(function (res) {
          if (res.data.error) {
            alert(res.data.error)
          }
          _self.summaryData = res.data
        })
    },
  },

  created: function () {
    let _self = this

    //获取实时数据
    axios.get(this.realTimeDataApi)
      .then(function (res) {
        _self.realTimeData = res.data
      })
  },

  mounted: function () {
    let _self = this

    //获取总览数据
    axios.get(this.summaryDataApi)
      .then(function (res) {
        _self.summaryData = res.data
        _self.loading = false
      })
  },
})