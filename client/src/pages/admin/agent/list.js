import '../common.js'
import FilterBar from '../../../components/FilterBar.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'
import DetailRow from './components/DetailRow.vue'

Vue.component('table-actions', TableActions)
Vue.component('detail-row', DetailRow)

new Vue({
  el: '#app',
  components: {
    FilterBar,
    MyVuetable,
    MyToastr,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {
      group: '',
      parent: '',
      topUpType: 1,
    },       //待编辑的行
    agentType: {
      2: '总代理',
      3: '钻石代理',
      4: '金牌代理',
    },
    topUpData: {
      type: {
        1: '房卡',
        2: '金币',
      },
      typeId: 1,
      amount: null,
    },
    changePassword: {       //修改用户密码
      password: '',
    },
    topUpApiPrefix: '/admin/api/top-up/agent',
    editApiPrefix: '/admin/api/agent',
    updatePassApiPrefix: '/admin/api/agent/pass',
    deleteApiPrefix: '/admin/api/agent',
    tableClass: 'row',

    tableUrl: '/admin/api/agent',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        sortField: 'id',
      },
      {
        name: 'name',
        title: '昵称',
      },
      {
        name: 'account',
        title: '登录账号',
        sortField: 'account',
      },
      {
        name: 'group.name',
        title: '代理级别',
        sortField: 'group_id',
      },
      {
        name: 'parent.account',
        title: '上级代理',
      },
      {
        name: 'inventorys',
        title: '房卡数量',
        callback: 'getCardsCount',
      },
      {
        name: 'inventorys',
        title: '金币数量',
        callback: 'getCoinsCount',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    callbacks: {
      getCardsCount (inventorys) {
        if (0 === inventorys.length) {
          return null
        }
        for (let inventory of inventorys) {
          if (inventory.item.name === '房卡') {
            return inventory.stock
          }
        }
      },
      getCoinsCount (inventorys) {
        if (0 === inventorys.length) {
          return null
        }
        for (let inventory of inventorys) {
          if (inventory.item.name === '金币') {
            return inventory.stock
          }
        }
      },
    },
  },

  methods: {
    topUpAgent () {
      let _self = this
      let toastr = this.$refs.toastr

      axios({
        method: 'POST',
        url: `${_self.topUpApiPrefix}/${_self.activatedRow.account}/${_self.topUpData.typeId}/${_self.topUpData.amount}`,
        validateStatus: function (status) {
          return status === 200 || status === 422 //定义哪些http状态返回码会被promise resolve
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
          }
        })
    },

    editAgentInfo () {
      let _self = this
      let toastr = this.$refs.toastr

      axios({
        method: 'PUT',
        url: `${_self.editApiPrefix}/${_self.activatedRow.id}`,
        data: _self.activatedRow,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            return toastr.message(JSON.stringify(response.data), 'error')
          }
          return toastr.message(response.data.message)
        })
    },

    updateAgentPassword () {
      let _self = this
      let toastr = this.$refs.toastr

      axios({
        method: 'PUT',
        url: `${_self.updatePassApiPrefix}/${_self.activatedRow.id}`,
        data: _self.changePassword,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            return toastr.message(JSON.stringify(response.data), 'error')
          }
          return toastr.message(response.data.message)
        })
    },

    deleteAgent () {
      let _self = this
      let toastr = this.$refs.toastr

      axios({
        method: 'DELETE',
        url: `${_self.deleteApiPrefix}/${_self.activatedRow.id}`,
      })
        .then(function (response) {
          response.data.error
            ? toastr.message(response.data.error, 'error')
            : toastr.message(response.data.message)

          //删除完成用户之后重新刷新表格数据，避免被删除用户继续留存在表格中
          _self.$root.eventHub.$emit('vuetableRefresh')
        })
    },
  },

  mounted: function () {
    let _self = this

    //判断屏幕大小，更新div的class，使table在手机浏览器下带上滚动条
    let windowWidth = document.body.clientWidth
    if (windowWidth < 768) {
      this.tableClass = 'row pre-scrollable'
    }

    this.$root.eventHub.$on('topUpAgentEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('editInfoEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('changeAgentPasswordEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteAgentEvent', (data) => _self.activatedRow = data)
  },
})