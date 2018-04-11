import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/MyFilterBar.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    FilterBar,
    MyToastr,
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
    topUpConfirmation: false,

    tableUrl: '/admin/api/game/players',
    tableTrackBy: 'id',
    tableFields: [
      {
        name: 'id',
        title: '玩家ID',
        sortField: 'id',
      },
      {
        name: 'nickname',
        title: '玩家昵称',
      },
      {
        name: 'ycoins',
        title: '房卡数量',
        sortField: 'ycoins',
      },
      // {
      //   name: 'ypoints',
      //   title: '金币数量',
      // },
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
        field: 'id',
        sortField: 'id',
        direction: 'desc',
      },
    ],
  },

  methods: {
    topUpPlayer () {
      let _self = this
      let apiUrl = `/admin/api/top-up/player/${_self.activatedRow.id}/${_self.topUpData.typeId}/${_self.topUpData.amount}`
      let toastr = this.$refs.toastr

      axios({
        method: 'POST',
        url: apiUrl,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            toastr.message(JSON.stringify(response.data), 'error')
          } else {
            response.data.error
              ? toastr.message(response.data.error, 'error')
              : toastr.message(response.data.message)
            _self.topUpData.amount = null
            _self.topUpConfirmation = false
            _self.$root.eventHub.$emit('MyVuetable:refresh')  //重新刷新表格
          }
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  mounted: function () {
    let _self = this
    let toastr = this.$refs.toastr

    this.$root.eventHub.$on('topUpPlayerEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('MyVuetable:error', (data) => toastr.message(data.error, 'error'))
  },

  created () {
    let playerId = myTools.getQueryString('player_id')
    if (playerId) {   //从牌艺馆页面跳转过来时要查询一次，直接打开的就不查询
      this.tableUrl += '?player_id=' + playerId
    }
  },
})
