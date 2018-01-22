import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    addCommunityForm: {},
    communityApi: '/agent/api/community',

    tableUrl: '/agent/api/community',
    tableFields: [
      {
        name: 'id',
        title: 'id',
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
  },

  created: function () {
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('manageCommunityEvent', this.onManageCommunity)
    this.$root.eventHub.$on('deleteCommunityEvent', (data) => _self.activatedRow = data)
  },
})