import { myTools } from '../index.js'
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
    itemTypeData: [],
    itemTypeOptions: {
      1: '房卡',
      2: '金币',
    },
    item: null,
    item_type_name: null,     //创建订单默认选中值
    item_amount: null,
    totalFee: null,
    createOrderData: {
      order_creator_type: 2,    //代理商订单
      trade_type: 'NATIVE',     //扫码支付
      item_amount: null,
      item_type_id: 1,          //默认为房卡，道具id为1
    },

    userInfoApi: '/api/info',
    itemTypeDataApi: '/api/order/item',
    orderCreationApi: '/api/wechat/order',
    tableUrl: '/api/wechat/order',
    tableFields: [
      {
        name: 'id',
        title: 'id',
        sortField: 'id',
      },
      {
        name: 'order_creator_type_name',
        title: '创建者类型',
      },
      {
        name: 'order_creator_name',
        title: '创建者账号',
      },
      {
        name: 'item_type_name',
        title: '道具类型',
      },
      {
        name: 'item_amount',
        title: '道具数量',
      },
      {
        name: 'total_fee_yuan',
        title: '订单金额(元)',
      },
      {
        name: 'trade_type_name',
        title: '支付类型',
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
    item_type_name: function (val) {
      let item = _.find(this.itemTypeData, { name: val})
      this.item = item
      this.totalFee = item.price * this.item_amount / 100
      this.createOrderData.item_type_id = item.id    //返回道具的id号
    },

    item_amount: function (val) {
      this.totalFee = this.item.price * val / 100
      this.createOrderData.item_amount = val
    },
  },

  created () {
    let _self = this

    myTools.axiosInstance.get(this.itemTypeDataApi)
      .then(function (res) {
        _self.itemTypeData = res.data
        _self.item_type_name = res.data[0].name   //创建订单时默认选中第一个道具
      })
      .catch(function (err) {
        alert(err)
      })

    myTools.axiosInstance.get(this.userInfoApi)
      .then(function (res) {
        _self.agentInfo = res.data
        _self.createOrderData.order_creator_id = res.data.id
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    this.$root.eventHub.$on('orderPaymentEvent', this.onOrderPaymentEvent)
    this.$root.eventHub.$on('orderCancellationEvent', (data) => this.activatedRow = data)
  },
})