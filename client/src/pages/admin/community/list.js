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
    auditCommunityApproval: '通过',
    auditCommunityGameGroup: '',
    auditCommunityForm: {
      'approval': '通过',
      'game_group_id': '',
    },
    addCommunityForm: {},
    editCommunityForm: {},
    type_id: '',
    gameGroupIdNameMap: {},

    editCommunityApi: '/admin/api/community',
    gameGroupIdNameMapApi: '/admin/api/community/game-group/id-name-map',
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
        callback: 'convertPlayer',
      },
      {
        name: 'owner_agent',
        title: '代理商',
        callback: 'convertAgent',
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
        name: 'game_group_name',
        title: '游戏包',
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

      convertAgent (value) {
        let agentListApi = '/admin/agent/list'
        return `<a href="${agentListApi}?agent_id=${value.id}">${value.account}</a>`
      },

      convertPlayer (value) {
        let playerListApi = '/admin/player/list'
        return `<a href="${playerListApi}?player_id=${value}">${value}</a>`
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
    onEditItem (data){
      this.editCommunityForm = _.cloneDeep(data)
      this.editCommunityForm.name = data.name
      this.editCommunityForm.info = data.info
      this.editCommunityForm.game_group = this.gameGroupIdNameMap[data.game_group]
    },
    editCommunity (){
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.communityApi}/${_self.editCommunityForm.id}`

      let game_group = _.findKey(this.gameGroupIdNameMap, (v) => v === _self.editCommunityForm.game_group)

      myTools.axiosInstance.put(api, {
        'name': _self.editCommunityForm.name,
        'info': _self.editCommunityForm.info,
        'game_group': game_group,
      })
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
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
      this.auditCommunityForm.approval = _.findKey(this.auditCommunityMap, (v) => v === _self.auditCommunityApproval)
      this.auditCommunityForm.game_group_id = _.findKey(this.gameGroupIdNameMap, (v) => v === _self.auditCommunityGameGroup)

      myTools.axiosInstance.post(api, this.auditCommunityForm)
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
      let type_id = this.type_id
      this.tableUrl = '/admin/api/community?status=' + status + '&type_id=' + type_id
    },
  },

  created: function () {
    let _self = this
    myTools.axiosInstance.get(this.gameGroupIdNameMapApi)
      .then(function (res) {
        _self.gameGroupIdNameMap = res.data
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('auditMemberEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteCommunityEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('editCommunityEvent', this.onEditItem)
  },
})