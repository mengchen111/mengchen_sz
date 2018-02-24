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
    eventHub: new Vue(),
    dateFormat: 'YYYY-MM-DD',
    date: moment().format('YYYY-MM-DD'),
    statement: {
      task: [],
      reward: [],
    },

    statementApi: '/admin/api/activities/statement',
  },

  methods: {
    changeDate () {
      this.fetchData(this.date)
    },

    fetchData (date) {
      let _self = this
      let toastr = this.$refs.toastr
      let formData = {
        date: date,
      }

      myTools.axiosInstance.get(this.statementApi, {
        params: formData,
      })
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.statement = res.data
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  created: function () {
    //
  },

  mounted: function () {
    this.fetchData(this.date)
  },
})