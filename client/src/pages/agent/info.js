import { myTools } from './index.js'
import MyToastr from '../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
  },
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
      let toastr = this.$refs.toastr

      myTools.axiosInstance.put(this.editInfoApi, this.currentAgentInfo)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          //清空表单数据
          for (let index of _.keys(_self.formData)) {
            _self.formData[index] = ''
          }
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  created: function () {
    let _self = this

    myTools.axiosInstance.get(_self.infoApi)
      .then(function (response) {
        _self.currentAgentInfo = response.data
      })
  },
})