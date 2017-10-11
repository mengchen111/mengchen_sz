import Vue from 'vue'
import axios from 'axios'

new Vue({
  el: '#app',
  data: {
    formData: {
      group_id: 2,
    },
    agentCreateApi: '/admin/api/agent',
  },
  methods: {
    createAgent () {
      let _self = this

      axios({
        method: 'POST',
        url: _self.agentCreateApi,
        data: _self.formData,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            alert(JSON.stringify(response.data))
          } else {
            response.data.error ? alert(response.data.error) : alert('添加代理商成功')

            //清空表单数据
            for (let index of Object.keys(_self.formData)) {
              _self.formData[index] = index === 'group_id' ? _self.formData[index] : ''
            }
          }
        })
    },
  },
})