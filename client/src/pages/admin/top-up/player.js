import '../index.js'
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
    tableUrl: '/admin/api/top-up/player',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        sortField: 'id',
      },
      {
        name: 'provider.account',
        title: '发放者(代理商)',
        sortField: 'provider_id',
      },
      {
        name: 'player',
        title: '玩家',
        sortField: 'player',
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