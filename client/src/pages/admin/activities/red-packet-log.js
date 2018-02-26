import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import FilterBar from '../../../components/MyFilterBar.vue'
import DetailRow from './components/RedPacketLogDetailRow.vue'
import RedPacketLogTableActions from './components/RedPacketLogTableActions.vue'
import vSelect from 'vue-select'

Vue.component('detail-row', DetailRow)
Vue.component('table-actions', RedPacketLogTableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    FilterBar,
    vSelect,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    editStatusOptionsMap: {
      '3': '已补发',
      '2': '发送失败',
      //'1': '发送成功'
    },
    editStatusValue: '已补发',
    editStatusApiPrifix: '/admin/api/activities/red-packet-log/status/',

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
        sortField: 'send_status',
        callback: 'convertSendStatus',
      },
      {
        name: 'updated_at',
        title: '发送时间',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    tableCallbacks: {
      convertTotalAmount (value) {
        return value / 100  //分转为元
      },
      convertSendStatus (value) {
        let statusMap = ['待发送', '已发送', '发送失败', '已补发']
        return statusMap[value]
      },
    },
  },

  computed: {
    editStatusOptions: function () {
      return _.values(this.editStatusOptionsMap)
    },
  },

  methods: {
    changeStatus () {
      let toastr = this.$refs.toastr
      let _self = this

      if (! this.editStatusValue) {
        return toastr.message('状态不能为空', 'error')
      }

      let data = {
        send_status: _.findKey(this.editStatusOptionsMap, (v) => v === this.editStatusValue),
      }
      let api = this.editStatusApiPrifix + this.activatedRow.id

      myTools.axiosInstance.put(api, data)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  created: function () {
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editStatusEvent', (data) => _self.activatedRow = data)
  },
})