import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import MyFilterBar from '../../../components/MyFilterBar.vue'
import DetailRow from './components/DetailRow.vue'
import TableActions from './components/TableActions.vue'
import vSelect from 'vue-select'

Vue.component('detail-row', DetailRow)
Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
    MyFilterBar,
    vSelect,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: null,
    agentInfo: {},
    wxTopUpRuleData: [],
    rule_price: null,
    totalFee: null,


    createOrderData: {},

    wxTopUpRuleApi: '/agent/api/wx-top-up-rules',
    orderCreationApi: '/api/wechat/order',
    tableUrl: '/api/wechat/order/agent',
    tableFields: [
      {
        name: 'id',
        title: 'id',
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
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
  },

  methods: {
    createOrder () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.orderCreationApi, this.createOrderData)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    cancelOrder () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.tableUrl + '/' + this.activatedRow.id)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(err => alert(err))
    },

    onOrderPaymentEvent (data) {
      let _self = this

      //请求单个订单数据（含有支付二维码）
      myTools.axiosInstance.get(this.tableUrl + '/' + data.id)
        .then(function (res) {
          _self.activatedRow = res.data
          jQuery('#order-payment-modal-button').click()   //弹出支付框
        })
    },
  },

  watch: {
    rule_price: function (val) {
      let rule = _.find(this.wxTopUpRuleData, (v) => v.id === val)
      this.totalFee = rule.price_yuan
      this.createOrderData.wx_top_up_rule_id = val

    },
  },

  created () {
    let _self = this

    myTools.axiosInstance.get(this.wxTopUpRuleApi)
      .then(function (res) {
        _self.wxTopUpRuleData = res.data
        // _self.item_type_name = res.data[0].name   //创建订单时默认选中第一个道具
      })
      .catch(function (err) {
        alert(err)
      })

  },

  mounted: function () {
    this.$root.eventHub.$on('orderPaymentEvent', this.onOrderPaymentEvent)
    // this.$root.eventHub.$on('orderCancellationEvent', (data) => this.activatedRow = data)
  },
})