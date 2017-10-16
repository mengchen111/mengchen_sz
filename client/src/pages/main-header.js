new Vue({
  el: '.main-header',
  data: {
    adminInfo: {            //当前登录的管理员信息
      account: 'admin',
    },
    inventoryAmount: {
      cards: 0,
      coins: 0,
    },
    logoutApi: '/logout',
    infoApi: '/api/info',
    loading: true,
  },

  methods: {
    logoutAction () {
      axios.post(this.logoutApi)
        .then(() => window.location.href = '/')
    },
  },

  mounted: function () {
    let _self = this
    axios.get(this.infoApi)
      .then(function (response) {
        _self.adminInfo = response.data

        if (_self.adminInfo.inventorys.length > 0) {
          for (let inventory of _self.adminInfo.inventorys) {
            switch (inventory.item.name) {
              case '房卡':
                _self.inventoryAmount.cards = inventory.stock
                break
              case '金币':
                _self.inventoryAmount.coins = inventory.stock
                break
              default:
                break
            }
          }
        }

        _self.loading = false
      })
  },
})

new Vue({
  el: '#change-password-modal',
  data: {
    formData: {
      password: '',
      new_password: '',
      new_password_confirmation: '',
    },
  },

  methods: {
    changePasswordAction () {
      let _self = this
      let role = location.href.match(/http:\/\/[\w.-]+\/([\w-]+\/)/)[1]   //管理员还是代理商
      let changePassApi = `/${role}api/self/password`

      axios({
        method: 'PUT',
        url: changePassApi,
        data: _self.formData,
        timeout: 5000,                          //超时时间
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
        validateStatus: function (status) {     //定义哪些http状态返回码会被promise resolve
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            return alert(JSON.stringify(response.data))
          }
          response.data.error ? alert(response.data.error) : alert(response.data.message)
          for (let index of Object.keys(_self.formData)) {
            _self.formData[index] = ''
          }
        })
    },
  },
})