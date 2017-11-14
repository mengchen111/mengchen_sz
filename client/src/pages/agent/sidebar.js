new Vue({
  el: '#sidebar',
  data: {
    isTopAgent: false,
    isGoldAgent: false,
    uri: {
      home: {
        isActive: false,
      },
      player: {
        isActive: false,
        'top-up': {
          isActive: false,
        },
      },
      stock: {
        isActive: false,
        'apply-request': {
          isActive: false,
        },
        'apply-history': {
          isActive: false,
        },
      },
      subagent: {
        isActive: false,
        list: {
          isActive: false,
        },
        create: {
          isActive: false,
        },
      },
      'top-up': {
        isActive: false,
        child: {
          isActive: false,
        },
        player: {
          isActive: false,
        },
      },
      info: {
        isActive: false,
      },
    },
  },

  created: function () {
    let _self = this
    let agentTypeApi = '/agent/api/self/agent-type'
    let accessedUri = location.href.match(/http:\/\/[\w.-]+\/agent\/([\w/-]+)/)[1]
      .split('/')

    //不同的代理商级别显示不同的菜单
    axios.get(agentTypeApi)
      .then(function (response) {
        _self.isTopAgent = '总代理' === response.data.name
        _self.isGoldAgent = '黄金代理' === response.data.name
      })

    //被访问的页面的菜单项会被设置为active
    accessedUri.reduce(function (lastValue, currentValue) {
      lastValue[currentValue].isActive = true
      return lastValue[currentValue]
    }, _self.uri)
  },
})