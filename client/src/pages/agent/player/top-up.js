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
    itemType: {
      1: '房卡',
      //2: '金币',  //暂不开放金币
    },
    typeValue: '房卡',
    topUpData: {
      typeId: 1,
      playerId: null,
      amount: null,
    },
    topUpApiPrefix: '/agent/api/top-up/player',
  },

  computed: {
    options: function () {
      return _.values(this.itemType)
    },
  },

  watch: {
    typeValue: function (val) {
      this.topUpData.typeId = _.findKey(this.itemType, (o) => o === val)
    },
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