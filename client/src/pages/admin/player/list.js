import '../common.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/FilterBar.vue'
import TableActions from './components/TableActions.vue'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    topUpData: {
      type: {
        1: '房卡',
        2: '金币',
      },
      typeId: 1,
      amount: null,
    },

    tableUrl: '/admin/api/game/players',
    tableTrackBy: 'uid',
    tableFields: [
      {
        name: 'uid',
        title: '玩家ID',
      },
      {
        name: 'nickname',
        title: '玩家昵称',
      },
      {
        name: 'ycoins',
        title: '房卡数量',
      },
      {
        name: 'ypoints',
        title: '金币数量',
      },
      {
        name: 'state',
        title: '账号状态',
      },
      {
        name: 'create_time',
        title: '创建时间',
      },
      {
        name: 'last_time',
        title: '最后登录时间',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    tableSortOrder: [      //默认的排序
      {
        field: 'uid',
        sortField: 'uid',
        direction: 'desc',
      },
    ],
  },

  methods: {
    topUpPlayer () {
      let _self = this
      let apiUrl = `/admin/api/top-up/player/${_self.activatedRow.uid}/${_self.topUpData.typeId}/${_self.topUpData.amount}`

      axios({
        method: 'POST',
        url: apiUrl,
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
            //_self.$root.eventHub.$emit('vuetableRefresh')  //重新刷新表格
          }
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  mounted: function () {
    let _self = this

    this.$root.eventHub.$on('topUpPlayerEvent', function (data) {
      _self.activatedRow = data
    })
    this.$root.eventHub.$on('vuetableDataError', (data) => alert(data.error))
  },
})
