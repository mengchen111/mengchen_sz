import '../common.js'

new Vue({
  el: "#app",
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

      axios({
        method: 'POST',
        url: `${_self.topUpApiPrefix}/${_self.topUpData.playerId}/${_self.topUpData.typeId}/${_self.topUpData.amount}`,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            alert(JSON.stringify(response.data))
          } else {
            response.data.error ? alert(response.data.error) : alert(response.data.message)
            _self.topUpData.amount = null
          }
        })
    },
  },
})