import '../index.js'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
  },
  data: {
    stockApplyApi: '/admin/api/stock',
    stockApplyData: {
      type: {
        1: '房卡',
        2: '金币',
      },
      item_id: 1,
      amount: null,
      remark: null,
    },
  },
  methods: {
    stockApply () {
      let _self = this
      let toastr = this.$refs.toastr

      axios({
        method: 'POST',
        url: _self.stockApplyApi,
        data: _self.stockApplyData,
        validateStatus: function (status) {
          return status == 200 || status == 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            toastr.message(JSON.stringify(response.data), 'error')
          } else {
            response.data.error
              ? toastr.message(response.data.error, 'error')
              : toastr.message(response.data.message)
            _self.stockApplyData.amount = null
            _self.stockApplyData.remark = null
          }
        })
    },
  },
})