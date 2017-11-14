new Vue({
  el: '#sidebar',
  data: {
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
      system: {
        isActive: false,
        log: {
          isActive: false,
        },
      },
    },
  },

  created: function () {
    let _self = this
    let accessedUri = location.href.match(/http:\/\/[\w.-]+\/admin\/([\w/-]+)/)[1]
      .split('/')

    //被访问的页面的菜单项会被设置为active
    accessedUri.reduce(function (lastValue, currentValue) {
      lastValue[currentValue].isActive = true
      return lastValue[currentValue]
    }, _self.uri)
  },
})