import '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/MyFilterBar.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),

    tableUrl: '/agent/api/community/card/top-up-history?item_type_id=1',
    tableFields: [
      {
        name: 'community_id',
        title: '社区id',
      },
      {
        name: 'item_amount',
        title: '房卡数量',
      },
      {
        name: 'created_at',
        title: '充值时间',
      },
    ],
  },
})