import '../common.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import TableActions from './components/MemberTableActions.vue'
import vSelect from 'vue-select'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
    vSelect,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    groupMap: {},
    groups: [],   //select组件用到的options数组
    searchGroupOptions: [],
    searchGroupValue: '全部组',
    createMemberData: {},
    editMemberData: {},
    searchGroup: '所有组',

    groupMapApi: '/admin/api/group/map',
    memberApiPrefix: '/admin/api/role',
    memberListApi: '/admin/api/role',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
      },
      {
        name: 'name',
        title: '昵称',
      },
      {
        name: 'account',
        title: '账号',
      },
      {
        name: 'group.name',
        title: '所属组',
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
    createMember () {
      let _self = this
      let toastr = this.$refs.toastr

      if (! this.createMemberData.group) {
        return toastr.message('未选择组', 'error')
      }

      this.createMemberData.group_id = _.findKey(this.groupMap, function (value) {
        return value === _self.createMemberData.group
      })

      axios({
        method: 'POST',
        url: this.memberApiPrefix,
        data: this.createMemberData,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            return toastr.message(JSON.stringify(response.data), 'error')
          }
          if (response.data.error) {
            return toastr.message(response.data.error, 'error')
          }
          toastr.message(response.data.message)
          _self.createMemberData = {}
          _self.$root.eventHub.$emit('MyVuetable:refresh')  //重新刷新表格
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteMember () {
      let _self = this
      let toastr = this.$refs.toastr

      axios.delete(this.memberApiPrefix + '/' + this.activatedRow.id)
        .then(function (res) {
          res.data.error
            ? toastr.message(res.data.error, 'error')
            : toastr.message(res.data.message)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          toastr.message(err, 'error')
        })
    },

    editMember () {
      let _self = this
      let toastr = this.$refs.toastr

      if (! this.editMemberData.group) {
        return toastr.message('未选择组', 'error')
      }

      if (this.editMemberData.password === '') {
        delete this.editMemberData.password
      }

      this.editMemberData.group_id = _.findKey(this.groupMap, function (value) {
        return value === _self.editMemberData.group
      })

      axios({
        method: 'PUT',
        url: this.memberApiPrefix + '/' + this.activatedRow.id,
        data: this.editMemberData,
        validateStatus: function (status) {
          return status === 200 || status === 422
        },
      })
        .then(function (response) {
          if (response.status === 422) {
            return toastr.message(JSON.stringify(response.data), 'error')
          }
          if (response.data.error) {
            return toastr.message(response.data.error, 'error')
          }
          toastr.message(response.data.message)
          _self.editMemberData = {}
          _self.$root.eventHub.$emit('MyVuetable:refresh')  //重新刷新表格
        })
        .catch(function (err) {
          alert(err)
        })
    },

    searchGroupCallback (value) {
      if (value === this.searchGroupValue) {
        return this.memberListApi = this.memberApiPrefix
      }

      let groupId = _.findKey(this.groupMap, function (v) {
        return v === value
      })

      this.memberListApi = this.memberApiPrefix + '?group_id=' + groupId
    },

    onEditMember (data) {
      this.activatedRow = data
      this.editMemberData.name = this.activatedRow.name
      this.editMemberData.account = this.activatedRow.account
      this.editMemberData.group = this.activatedRow.group.name
    },
  },

  created: function () {
    let _self = this
    let toastr = this.$refs.toastr

    axios.get(this.groupMapApi)
      .then(function (res) {
        _self.groupMap = res.data
        _self.groups = _.values(_self.groupMap)
        _self.searchGroupOptions = _.cloneDeep(_self.groups)
        _self.searchGroupOptions.unshift(_self.searchGroupValue)
      })
      .catch(function (err) {
        toastr.message(err, 'error')
      })
  },

  mounted: function () {
    let _self = this

    this.$root.eventHub.$on('editMemberEvent', this.onEditMember)
    this.$root.eventHub.$on('deleteMemberEvent', (data) => _self.activatedRow = data)
  },
})