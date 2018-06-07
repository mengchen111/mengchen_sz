import shownMenu from './sidebarMenuShown.js'

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
        room: {
          isActive: false,
        },
        'online-players': {
          isActive: false,
        },
      },
      gm: {
        isActive: false,
        room: {
          isActive: false,
        },
        record: {
          isActive: false,
        },
        marquee: {
          isActive: false,
        },
      },
      activities: {
        isActive: false,
        'activities-list': {
          isActive: false,
        },
        'rewards-list': {
          isActive: false,
        },
        'goods-list': {
          isActive: false,
        },
        'tasks-list': {
          isActive: false,
        },
        'user-goods': {
          isActive: false,
        },
        'player-task': {
          isActive: false,
        },
        statement: {
          isActive: false,
        },
        'log-activity-reward': {
          isActive: false,
        },
        'red-packet-log': {
          isActive: false,
        },
      },
      community: {
        isActive: false,
        list: {
          isActive: false,
        },
        'valid-card': {
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
        bills: {  //售卡记录
          isActive: false,
        },
        'valid-card': {
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
      order: {
        isActive: false,
        wechat: {
          isActive: false,
        },
        withdrawals: {
          isActive: false,
        },
        rebates: {
          isActive: false,
        },
        search: {
          isActive: false,
        },
      },
      rules: {
        isActive: false,
        'wx-top-up': {
          isActive: false,
        },
        rebate: {
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

    shownMenu: shownMenu,

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

      //自定义assignWith的处理函数
      let customizer = function (objValue, srcValue) {
        if (typeof objValue === 'object') {
          return _.assignWith({}, objValue, srcValue, customizer)
        } else {
          return undefined
          //assignWith的customizer函数返回undefined则使用assign默认规则替换，等同于return srcValue
        }
      }

      axios.get(this.viewAccessApi)
        .then(function (res) {
          _self.shownMenu = _.assignWith({}, _self.shownMenu, res.data.view_access, customizer)  //merge
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