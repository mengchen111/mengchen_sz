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
    statusDefaultValue: '全部',
    addCommunityForm: {},
    communityApi: '/agent/api/community',

    tableUrl: '/agent/api/community?status=1',  //默认显示已审核
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
        name: 'name',
        title: '名称',
      },
      // {
      //   name: 'info',
      //   title: '简介',
      // },
      // {
      //   name: 'card_stock',
      //   title: '房卡',
      // },
      {
        name: 'members_count',
        title: '成员数',
      },
      // {
      //   name: 'created_at',
      //   title: '创建时间',
      // },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
  },

  methods: {
    onManageCommunity (data) {
      //跳转到管理页面上去
      window.location.href = 'manage?community=' + data.id
    },

    addCommunity () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.communityApi, this.addCommunityForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
          _self.addCommunityForm.owner_player_id = ''
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

    onSelectChange (value) {
      let status = _.findIndex(this.statusOptions, (v) => v === value)
      this.tableUrl = '/agent/api/community?status=' + status
    },
  },

  created: function () {
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('manageCommunityEvent', this.onManageCommunity)
    this.$root.eventHub.$on('deleteCommunityEvent', (data) => _self.activatedRow = data)
  },
})