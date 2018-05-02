import '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'


new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    orderNo: null,
    userPrefixApi: '/api/wechat/order/search',

    tableUrl: '/api/wechat/order/search',
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'openid',
        title: 'openid',
      },
      {
        name: 'is_subscribe',
        title: '是否关注公众账号',
      },
      {
        name: 'trade_type',
        title: '交易类型',
      },
      {
        name: 'trade_state',
        title: '交易状态',
      },
      {
        name: 'bank_type',
        title: '付款银行',
      },
      {
        name: 'total_fee',
        title: '标价金额',
      },
    ],
  },
  methods: {
    getRecord () {
      let orderNo = this.orderNo ? this.orderNo : 0
      this.tableUrl = this.userPrefixApi + '/' + orderNo  //更改tableUrl之后vuetable会自动刷新数据

    },
  },

})