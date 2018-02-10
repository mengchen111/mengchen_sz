import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import FilterBar from '../../../components/MyFilterBar.vue'
import DetailRow from './components/RedPacketLogDetailRow.vue'

Vue.component('detail-row', DetailRow)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),

    tableUrl: '/admin/api/activities/red-packet-log',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        sortField: 'id',
      },
      {
        name: 'player_id',
        title: '玩家id',
      },
      {
        name: 'nickname',
        title: '玩家昵称',
      },
      {
        name: 'total_amount',
        title: '发送金额',
        callback: 'convertTotalAmount',
      },
      {
        name: 'send_status',
        title: '发送状态',
        callback: 'convertSendStatus',
      },
      {
        name: 'updated_at',
        title: '发送时间',
      },
    ],
    tableCallbacks: {
      convertTotalAmount (value) {
        return value / 100  //分转为元
      },
      convertSendStatus (value) {
        let statusMap = ['待发送', '已发送', '发送失败']
        return statusMap[value]
      },
    },
  },

  methods: {
  },

  created: function () {
  },

  mounted: function () {
  },
})