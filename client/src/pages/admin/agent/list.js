import {myTools} from '../index.js'
import FilterBar from '../../../components/MyFilterBar.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'
import DetailRow from './components/DetailRow.vue'
import vSelect from 'vue-select'

Vue.component('table-actions', TableActions)
Vue.component('detail-row', DetailRow)

new Vue({
  el: '#app',
  components: {
    FilterBar,
    MyVuetable,
    MyToastr,
    vSelect,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {
      group: '',
      parent: '',
      topUpType: 1,
    },       //待编辑的行
    agentType: {
      2: '总代',
      3: '钻石',
      4: '金牌',
    },
    agentTypeValue: null,
    itemType: {
      1: '房卡',
      //2: '金币',
    },
    itemTypeValue: '房卡',
    topUpData: {
      typeId: 1,
      amount: null,
    },
    topUpConfirmation: false,
    changePassword: {       //修改用户密码
      password: '',
    },
    topUpApiPrefix: '/admin/api/top-up/agent',
    editApiPrefix: '/admin/api/agent',
    updatePassApiPrefix: '/admin/api/agent/pass',
    deleteApiPrefix: '/admin/api/agent',

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
        title: '房卡库存',
        callback: 'getCardsCount',
      },
      // {
      //   name: 'inventorys',
      //   title: '金币库存',
      //   callback: 'getCoinsCount',
      // },
      {
        name: 'item_sold_total',
        title: '累计售卡',
        callback: 'getCardSoldTotal',
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
      getCardSoldTotal (v) {
        let cardTypeId = 1
        return v[cardTypeId]
      },
    },
  },

  computed: {
    itemTypeOptions: function () {
      return _.values(this.itemType)
    },
    agentTypeOptions: function () {
      return _.values(this.agentType)
    },
  },

  watch: {
    itemTypeValue: function (value) {
      this.topUpData.typeId = _.findKey(this.itemType, (o) => o === value)
    },
  },

  methods: {
    topUpAgent () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${this.topUpApiPrefix}/${this.activatedRow.account}/${this.topUpData.typeId}/${this.topUpData.amount}`

      myTools.axiosInstance.post(api)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          _self.topUpData.amount = null
          _self.topUpConfirmation = false
          _self.$root.eventHub.$emit('MyVuetable:refresh')  //重新刷新表格
        })
        .catch(function (err) {
          alert(err)
        })
    },

    editAgentInfo () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.editApiPrefix}/${_self.activatedRow.id}`
      let formData = {
        name: this.activatedRow.name,
        account: this.activatedRow.account,
        group_id: _.findKey(this.agentType, (o) => o === this.agentTypeValue),
        parent_account: this.activatedRow.parent.account,
      }

      myTools.axiosInstance.put(api, formData)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
        })
        .catch(function (err) {
          alert(err)
        })
    },

    updateAgentPassword () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.updatePassApiPrefix}/${_self.activatedRow.id}`

      myTools.axiosInstance.put(api, this.changePassword)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteAgent () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.deleteApiPrefix}/${_self.activatedRow.id}`

      myTools.axiosInstance.delete(api)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          //删除完成用户之后重新刷新表格数据，避免被删除用户继续留存在表格中
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  mounted: function () {
    let _self = this

    this.$root.eventHub.$on('topUpAgentEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('editInfoEvent', function (data) {
      _self.activatedRow = data
      _self.agentTypeValue = _self.activatedRow.group.name
    })
    this.$root.eventHub.$on('changeAgentPasswordEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteAgentEvent', (data) => _self.activatedRow = data)
  },
})