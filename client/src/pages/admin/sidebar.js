import Vue from 'vue'

new Vue({
  el: '#sidebar',
  data: {
    uri: {
      home: {
        isActive: false,
      },
      player: {
        isActive: false,
        list: {
          isActive: false,
        },
        top_up: {
          isActive: false,
        },
      },
      stock: {
        isActive: false,
        apply_request: {
          isActive: false,
        },
        apply_list: {
          isActive: false,
        },
        apply_history: {
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
      top_up: {
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

  methods: {},

  created: function () {
    let _self = this
    let accessedUri = location.href.match(/http:\/\/[\w.-]+\/admin\/([\w/-]+)/)[1]
      .replace('-', '_')
      .split('/')

    //被访问的页面的菜单项会被设置为active
    accessedUri.reduce(function (lastValue, currentValue) {
      lastValue[currentValue].isActive = true
      return lastValue[currentValue]
    }, _self.uri)
  },
})