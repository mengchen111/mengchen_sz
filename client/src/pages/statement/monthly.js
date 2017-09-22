/**
 * Created by liudian on 9/13/17.
 */

import Vue from 'vue'
import MyVuetable from '../../components/MyVuetable.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    tableUrl: '/admin/api/statement/monthly',
    tableFields: [
      {
        name: 'date',
        title: '日期',
      },
      {
        name: 'card_purchased',
        title: '房卡购买量',
      },
      {
        name: 'coin_purchased',
        title: '金币购买量'
      },
      {
        name: 'card_consumed',
        title: '房卡消耗量'
      },
      {
        name: 'coin_consumed',
        title: '金币消耗量'
      },
    ],
    tableSortOrder: [    //默认的排序
      {
        field: 'date',
        sortField: 'date',
        direction: 'desc',
      }
    ],
  },
});