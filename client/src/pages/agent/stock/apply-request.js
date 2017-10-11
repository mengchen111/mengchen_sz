import Vue from 'vue'
import axios from 'axios'

new Vue({
  el: '#app',
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

      axios({
        method: 'POST',
        url: _self.stockApplyApi,
        data: _self.stockApplyData,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            alert(JSON.stringify(response.data))
          } else {
            response.data.error ? alert(response.data.error) : alert(response.data.message)
            _self.stockApplyData.amount = null
            _self.stockApplyData.remark = null
          }
        })
    },
  },
})