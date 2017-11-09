import '../common.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/MyFilterBar.vue'

new Vue({
  el: '#app',
  components: {
    FilterBar,
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    tableUrl: '/admin/api/top-up/agent',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        sortField: 'id',
      },
      {
        name: 'provider.account',
        title: '发放者(上级代理)',
        sortField: 'provider_id',
      },
      {
        name: 'receiver.account',
        title: '接收者(下级代理)',
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