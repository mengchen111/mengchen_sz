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
    validCardConsumedRecordsApi: '/admin/api/agent/card/valid-consumed-list',

    tableUrl: '/admin/api/agent/card/valid-consumed-list?&account=0', //account设为0，获取空数据
    tableFields: [
      // 查找的就是代理商账号，此列无须显示
      // {
      //   name: 'provider_account',
      //   title: '代理商',
      // },
      // {
      //   name: 'provider_nickname',
      //   title: '代理商昵称',
      // },
      {
        name: 'player',
        title: '玩家id',
      },
      {
        name: 'player_nickname',
        title: '玩家昵称',
      },
      {
        name: 'amount',
        title: '充值数量',
      },
      {
        name: 'valid_card_consumed_num',
        title: '有效耗卡数量',
      },
      {
        name: 'created_at',
        title: '充值时间',
      },
    ],
  },
  methods: {
    getValidCardConsumedRecords () {
      //如果输入框为空，那么传递到后端的account值为0
      if (this.agentAccount) {
        this.tableUrl = this.validCardConsumedRecordsApi + '?account=' + this.agentAccount  //更改tableUrl之后vuetable会自动刷新数据
      } else {
        this.tableUrl = this.validCardConsumedRecordsApi + '?account=0'
      }
    },
  },

  created () {
    this.agentAccount = myTools.getQueryString('account')
    if (this.agentAccount) {
      this.tableUrl = this.validCardConsumedRecordsApi + '?account=' + this.agentAccount
    } else {
      this.tableUrl = this.validCardConsumedRecordsApi + '?account=0'
    }
  },
})