import '../common.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/FilterBar.vue'

new Vue({
  el: '#app',
  components: {
    FilterBar,
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    tableUrl: '/admin/api/top-up/admin',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        sortField: 'id',
      },
      {
        name: 'provider.account',
        title: '管理员(发放者)',
      },
      {
        name: 'receiver.account',
        title: '代理商账号(接收者)',
        sortField: 'receiver_id',
      },
      {
        name: 'item.name',
        title: '充值类型',
        sortField: 'type',
      },
      {
        name: 'amount',
        title: '充值数量',
        sortField: 'amount',
      },
      {
        name: 'created_at',
        title: '充值时间',
      },
    ],
  },

})