import { myTools } from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyVuetable from '../../../components/MyVuetable.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),

    agentAccount: null,
    cardSoldRecordsApi: '/admin/api/agent/bills',

    tableUrl: '/admin/api/agent/bills?item_type=1&account=0', //account设为0，获取空数据
    tableFields: [
      {
        name: 'receiver.account',
        title: '代理商',
      },
      {
        name: 'player',
        title: '玩家id',
      },
      {
        name: 'nick_name',
        title: '玩家昵称',
      },
      {
        name: 'amount',
        title: '充值数量',
      },
      {
        name: 'created_at',
        title: '充值时间',
      },
    ],
  },
  methods: {
    getCardSoldRecords () {
      //道具类型为1，只查询房卡
      this.tableUrl = this.cardSoldRecordsApi + '?item_type=1&account=' + this.agentAccount  //更改tableUrl之后vuetable会自动刷新数据
    },
  },

  created () {
    this.agentAccount = myTools.getQueryString('account')
    let itemType = myTools.getQueryString('item_type')
    if (itemType) {
      this.tableUrl = this.cardSoldRecordsApi + '?item_type=' + itemType + '&account=' + this.agentAccount
    }
  },
})