import {myTools} from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
  },
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
      let toastr = this.$refs.toastr

      if (_self.currentAgentInfo['group_id'] >= 4) {
        return alert('金牌代理无法创建下级代理商')
      }

      myTools.axiosInstance.post(this.createSubagentApi, this.formData)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          //清空表单数据
          for (let index of _.keys(_self.formData)) {
            _self.formData[index] = ''
          }
        })
    },
  },

  created: function () {
    let _self = this

    myTools.axiosInstance.get(this.userInfoApi)
      .then(function (res) {
        _self.currentAgentInfo = res.data
      })
  },
})