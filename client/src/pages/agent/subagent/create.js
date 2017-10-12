import '../common.js'

new Vue({
  el: '#app',
  data: {
    agentType: {
      2: '总代理',
      3: '钻石代理',
      4: '金牌代理',
    },
    formData: {},
    currentAgentInfo: {},
    createSubagentApi: '/agent/api/subagent',
    userInfoApi: '/api/info',

  },
  methods: {
    createAgent () {
      let _self = this

      if (_self.currentAgentInfo['group_id'] >= 4) {
        return alert('金牌代理无法创建下级代理商')
      }

      axios({
        method: 'POST',
        url: _self.createSubagentApi,
        data: _self.formData,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            alert(JSON.stringify(response.data))
          } else {
            response.data.error ? alert(response.data.error) : alert(response.data.message)

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

    axios.get(this.userInfoApi)
      .then(function (response) {
        _self.currentAgentInfo = response.data
      })
  },
})