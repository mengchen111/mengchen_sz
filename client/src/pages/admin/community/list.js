import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'
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
      '待审核', '已审核', '审核不通过', '全部',
    ],
    auditCommunityMap: {
      1: '通过',
      2: '拒绝',
    },
    statusDefaultValue: '待审核',
    auditCommunityValue: '通过',
    addCommunityForm: {},
    communityApi: '/admin/api/community',
    auditCommunityApi: '/admin/api/community/audit',

    tableUrl: '/admin/api/community?status=0',  //默认显示待审核
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'owner_player_id',
        title: '玩家id',
      },
      {
        name: 'owner_agent.account',
        title: '代理商',
      },
      {
        name: 'name',
        title: '名称',
      },
      {
        name: 'info',
        title: '简介',
      },
      {
        name: 'card_stock',
        title: '可用房卡',
      },
      {
        name: 'card_frozen',
        title: '冻结房卡',
      },
      {
        name: 'members_count',
        title: '成员数',
      },
      {
        name: 'created_at',
        title: '创建时间',
      },
      {
        name: 'status',
        title: '审核状态',
        callback: 'transStatus',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],

    tableCallbacks: {
      transStatus (value) {
        let statusMap = ['待审核', '已审核', '审核不通过']
        return statusMap[value]
      },
    },
  },

  computed: {
    auditCommunityOptions () {
      return _.values(this.auditCommunityMap)
    },
  },

  methods: {
    addCommunity () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.communityApi, this.addCommunityForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
          _self.addCommunityForm.owner_player_id = ''
          _self.addCommunityForm.owner_agent_id = ''
          _self.addCommunityForm.name = ''
          _self.addCommunityForm.info = ''
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteCommunity () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.communityApi}/${_self.activatedRow.id}`

      myTools.axiosInstance.delete(api)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    auditCommunity () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.auditCommunityApi}/${_self.activatedRow.id}`
      let status = _.findKey(this.auditCommunityMap, (v) => v === this.auditCommunityValue)

      myTools.axiosInstance.post(api, {
        status: status,
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
      this.tableUrl = '/admin/api/community?status=' + status
    },
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('auditMemberEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteCommunityEvent', (data) => _self.activatedRow = data)
  },
})