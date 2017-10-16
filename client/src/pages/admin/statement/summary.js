import '../common.js'
import MyDatePicker from '../../../components/MyDatePicker.vue'

new Vue({
  el: '#app',
  components: {
    MyDatePicker,
  },
  data: {
    dateFormat: 'YYYY-MM-DD',
    date: '2017-10-11',

    loading: false,
    summaryDataApi: '/admin/api/statement/summary',
    summaryData: {},
  },
})