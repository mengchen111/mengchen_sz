import {myTools} from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
  },
  data: {
    stockApplyData: {
      type: {
        1: '房卡',
        2: '金币',
      },
      item_id: 1,
      amount: null,
      remark: null,
    },
    stockApplyApi: '/agent/api/stock',
  },
  methods: {
    stockApply () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.stockApplyApi, this.stockApplyData)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          _self.stockApplyData.amount = null
          _self.stockApplyData.remark = null
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },
})