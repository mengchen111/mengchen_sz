import {myTools} from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import vSelect from 'vue-select'

new Vue({
  el: '#app',
  components: {
    MyToastr,
    vSelect,
  },
  data: {
    agentType: {
      2: '总代理',
      3: '钻石代理',
      4: '金牌代理',
    },
    agentTypeValue: null,
    formData: {},
    currentAgentInfo: {},
    createSubagentApi: '/agent/api/subagent',
    userInfoApi: '/api/info',
  },

  computed: {
    options: function () {
      let options = []
      for (let [k, v] of _.entries(this.agentType)) {
        if (k > this.currentAgentInfo.group_id) {
          options.push(v)
        }
      }
      return options
    },
  },

  watch: {
    agentTypeValue: function (value) {
      this.formData.group_id = _.findKey(this.agentType, (v) => v === value)
    },
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