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
    searchingBalance: true,
    playerBalanceMsg: '',
    topUpData: {
      typeId: 1,
      playerId: null,
      amount: null,
    },
    topUpApiPrefix: '/agent/api/top-up/player',
    searchPlayerApi: '/api/game/player',
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

    searchBalance: _.debounce(function () {
      this.searchingBalance = true
      this.playerBalanceMsg = ''
      let _self = this

      if (! this.topUpData.playerId) {
        return true
      }

      myTools.axiosInstance.get(this.searchPlayerApi, {
        params: { player_id: _self.topUpData.playerId },
      })
        .then(function (res) {
          if (res.data.error) {
            _self.playerBalanceMsg = res.data.error
          } else {
            let player = res.data
            _self.playerBalanceMsg = '房卡余额: ' + player.ycoins
          }
          _self.searchingBalance = false
        })
        .catch(function (err) {
          alert(err)
        })
    }, 800),
  },
})