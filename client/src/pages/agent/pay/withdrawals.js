import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import vSelect from 'vue-select'
import MyDatePicker from '../../../components/MyDatePicker.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    vSelect,
    MyDatePicker,

  },
  data: {
    eventHub: new Vue(),
    dateFormat: 'YYYY-MM-DD',
    formData: {
      date: moment().format('YYYY-MM-DD'),
    },
    httpClient: myTools.axiosInstance,

    addForm: {
      contact_type: 0,
      amount: '500',
    },
    amountLimitData: [],
    amountLimitApi: '/agent/api/withdrawals/amount-limit',

    tableUrl: '/agent/api/withdrawals',  //默认显示已审核
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'amount',
        title: '金额',
      },
      {
        name: 'created_at',
        title: '提交日期',
      },
      {
        name: 'withdrawal_status',
        title: '状态',
      },
      {
        name: 'remark',
        title: '备注',
      },

    ],
  },

  methods: {
    store () {
      let _self = this
      let toastr = this.$refs.toastr

      this.httpClient.post(this.tableUrl, this.addForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    getStatement () {
      let date = this.formData.date
      // tableUrl 改变 前端 vue-table 会自动刷新
      this.tableUrl = '/agent/api/withdrawals?date=' + date
    },

  },

  created: function () {
    let _self = this

    this.httpClient.get(this.amountLimitApi)
      .then(function (res) {
        _self.amountLimitData = res.data
      })
  },
})