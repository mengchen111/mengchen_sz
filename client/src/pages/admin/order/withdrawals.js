import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/WithdrwalsTableActions.vue'
import vSelect from 'vue-select'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    vSelect,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    statusOptions: [
      '待审核', '待发放', '已发放', '审核拒绝', '全部',
    ],
    auditMap: {
      1: '待发放',
      2: '已发放',
      3: '审核拒绝',
    },
    statusDefaultValue: '待审核',
    auditValue: '待发放',
    user_id: '',
    remarkValue: '',

    auditApi: '/admin/api/withdrawals/audit',
    tableUrl: '/admin/api/withdrawals?status=0',  //默认显示待审核
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'user.name',
        title: '昵称',
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
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
  },

  computed: {
    auditOptions () {
      return _.values(this.auditMap)
    },
  },

  methods: {
    audit () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.auditApi}/${_self.activatedRow.id}`
      let status = _.findKey(this.auditMap, (v) => v === this.auditValue)
      let remark = _self.remarkValue
      myTools.axiosInstance.post(api, {
        status: status,
        remark: remark,
      })
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    onSelectChange (value) {
      let status = _.findIndex(this.statusOptions, (v) => v === value)
      let user_id = this.user_id
      this.tableUrl = '/admin/api/withdrawals?status=' + status + '&user_id=' + user_id
    },
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('auditMemberEvent', (data) => _self.activatedRow = data)
    // this.$root.eventHub.$on('deleteCommunityEvent', (data) => _self.activatedRow = data)
  },
})