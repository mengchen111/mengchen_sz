import {myTools} from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
  },
  data: {
    topUpData: {
      playerId: '',
      type: {
        1: '房卡',
        2: '金币',
      },
      typeId: 1,
      amount: null,
    },
    topUpApiPrefix: '/agent/api/top-up/player',
  },
  methods: {
    topUpPlayer () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${this.topUpApiPrefix}/${this.topUpData.playerId}/${this.topUpData.typeId}/${this.topUpData.amount}`

      myTools.axiosInstance.post(api)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          _self.topUpData.amount = null
        })
    },
  },
})