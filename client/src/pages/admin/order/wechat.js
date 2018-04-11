import '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import MyFilterBar from '../../../components/MyFilterBar.vue'
import DetailRow from './components/DetailRow.vue'
// import vSelect from 'vue-select'

Vue.component('detail-row', DetailRow)

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
    MyFilterBar,
    // vSelect,
  },
  data: {
    eventHub: new Vue(),

    tableUrl: '/api/wechat/order',
    tableFields: [
      {
        name: 'id',
        title: 'id',
        sortField: 'id',
      },
      {
        name: 'user_id',
        title: '代理商id',
      },
      {
        name: 'body',
        title: '订单说明',
      },
      {
        name: 'total_fee_yuan',
        title: '订单金额(元)',
      },
      {
        name: 'order_status_name',
        title: '订单状态',
      },
      {
        name: 'item_delivery_status_name',
        title: '发货状态',
      },
      {
        name: 'created_at',
        title: '创建时间',
      },
      {
        name: 'paid_at',
        title: '支付时间',
      },
    ],
  },

})