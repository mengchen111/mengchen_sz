new Vue({
  el: '#sidebar',
  data: {
    viewAccessApi: '/admin/api/group/authorization/view/0',

    uri: {
      home: {
        isActive: false,
      },
      statement: {
        isActive: false,
        summary: {
          isActive: false,
        },
      },
      gm: {
        isActive: false,
        record: {
          isActive: false,
        },
        room: {
          isActive: false,
        },
      },
      player: {
        isActive: false,
        list: {
          isActive: false,
        },
      },
      stock: {
        isActive: false,
        'apply-request': {
          isActive: false,
        },
        'apply-list': {
          isActive: false,
        },
        'apply-history': {
          isActive: false,
        },
      },
      agent: {
        isActive: false,
        create: {
          isActive: false,
        },
        list: {
          isActive: false,
        },
      },
      'top-up': {
        isActive: false,
        admin: {
          isActive: false,
        },
        agent: {
          isActive: false,
        },
        player: {
          isActive: false,
        },
      },
      permission: {
        isActive: false,
        member: {
          isActive: false,
        },
        group: {
          isActive: false,
        },
      },
      system: {
        isActive: false,
        log: {
          isActive: false,
        },
      },
    },

    shownMenu: {
      home: {
        ifShown: false,
      },
      statement: {
        ifShown: false,
        summary: {
          ifShown: false,
        },
      },
      gm: {
        ifShown: false,
        record: {
          ifShown: false,
        },
        room: {
          ifShown: false,
        },
      },
      player: {
        ifShown: false,
        list: {
          ifShown: false,
        },
      },
      stock: {
        ifShown: false,
        'apply-request': {
          ifShown: false,
        },
        'apply-list': {
          ifShown: false,
        },
        'apply-history': {
          ifShown: false,
        },
      },
      agent: {
        ifShown: false,
        create: {
          ifShown: false,
        },
        list: {
          ifShown: false,
        },
      },
      'top-up': {
        ifShown: false,
        admin: {
          ifShown: false,
        },
        agent: {
          ifShown: false,
        },
        player: {
          ifShown: false,
        },
      },
      permission: {
        ifShown: false,
        member: {
          ifShown: false,
        },
        group: {
          ifShown: false,
        },
      },
      system: {
        ifShown: false,
        log: {
          ifShown: false,
        },
      },
    },

    isAdmin: false,
  },

  methods: {
    activateMenu () {   //active 当前访问的uri在sidebar中的菜单项
      let _self = this
      let currentUrl = _.trim(location.href, '/')   //去掉尾部的'/'
      let currentUri = currentUrl.match(/http:\/\/[\w.-]+\/admin\/([\w/-]+)/)[1]
        .split('/')

      //被访问的页面的菜单项会被设置为active
      currentUri.reduce(function (lastValue, currentValue) {
        lastValue[currentValue].isActive = true
        return lastValue[currentValue]
      }, _self.uri)
    },

    setupMenu () {    //为不同的角色显示不同的menu菜单
      let _self = this

      axios.get(this.viewAccessApi)
        .then(function (res) {
          _self.shownMenu = Object.assign(_self.shownMenu, res.data.view_access)  //merge
          _self.isAdmin = res.data.is_admin
        })
        .catch(function (err) {
          alert('sidebar: ', err)
        })
    },
  },

  created: function () {
    this.activateMenu()
    this.setupMenu()
  },
})