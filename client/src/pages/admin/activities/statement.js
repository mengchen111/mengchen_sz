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
  },

  methods: {
    changeDate () {
      //todo 获取数据 双向绑定
      console.log(this.date, 'change date')
    },
  },

  created: function () {
    //
  },

  mounted: function () {
    //
  },
})