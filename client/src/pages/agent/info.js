import './common.js'

new Vue({
  el: '#app',
  data: {
    formData: {},
    currentAgentInfo: {
      parent: {},     //需要提前定义好，不然当访问页面时，未加载ajax之前获取不到属性名报错
      group: {},
    },
    editInfoApi: '/agent/api/self/info',
    infoApi: '/api/info',
  },

  methods: {
    editInfo () {
      let _self = this

      axios({
        method: 'PUT',
        url: _self.editInfoApi,
        data: _self.currentAgentInfo,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            alert(JSON.stringify(response.data))
          } else {
            response.data.error ? alert(response.data.error) : alert('信息更新成功')

            //清空表单数据
            for (let index of Object.keys(_self.formData)) {
              _self.formData[index] = ''
            }
          }
        })
    },
  },

  created: function () {
    let _self = this
    axios.get(_self.infoApi)
      .then(function (response) {
        _self.currentAgentInfo = response.data
      })
  },
})