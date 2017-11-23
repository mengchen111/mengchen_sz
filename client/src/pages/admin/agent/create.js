import '../index.js'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
  },
  data: {
    formData: {
      group_id: 2,
    },
    agentCreateApi: '/admin/api/agent',
  },
  methods: {
    createAgent () {
      let _self = this
      let toastr = this.$refs.toastr

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
            toastr.message(JSON.stringify(response.data), 'error')
          } else {
            response.data.error
              ? toastr.message(response.data.error, 'error')
              : toastr.message('添加代理商成功')

            //清空表单数据
            for (let index of _.keys(_self.formData)) {
              _self.formData[index] = index === 'group_id' ? _self.formData[index] : ''
            }
          }
        })
    },
  },
})