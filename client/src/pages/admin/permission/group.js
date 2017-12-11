import '../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import TableActions from './components/GroupTableActions.vue'
import shownMenu from '../sidebarMenuShown.js'
import {Checkbox} from 'vue-checkbox-radio'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
    Checkbox,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    createGroupData: {
      name: null,
    },
    currentGroupPermission: shownMenu,

    groupApiPrefix: '/admin/api/group',
    groupPermissionApiPrefix: '/admin/api/group/authorization/view',
    groupListApi: '/admin/api/group',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
      {
        name: 'name',
        title: '组名',
        titleClass: 'text-center',
        dataClass: 'text-center',
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
    createGroup () {
      let _self = this
      let toastr = this.$refs.toastr

      axios.post(this.groupApiPrefix, this.createGroupData)
        .then(function (res) {
          res.data.error
            ? toastr.message(res.data.error, 'error')
            : toastr.message(res.data.message)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
          _self.createGroupData.name = null
        })
        .catch(function (err) {
          toastr.message(err, 'error')
        })
    },

    editGroupName () {
      let _self = this
      let toastr = this.$refs.toastr

      axios.put(this.groupApiPrefix + '/' + this.activatedRow.id, this.activatedRow)
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

    editGroupPermission () {
      let toastr = this.$refs.toastr
      let data = {}
      data.view_access = JSON.stringify(this.currentGroupPermission)

      axios.put(this.groupPermissionApiPrefix + '/' + this.activatedRow.id, data)
        .then(function (res) {
          res.data.error
            ? toastr.message(res.data.error, 'error')
            : toastr.message(res.data.message)
        })
        .catch(function (err) {
          toastr.message(err, 'error')
        })
    },

    deleteGroup () {
      let _self = this
      let toastr = this.$refs.toastr

      axios.delete(this.groupApiPrefix + '/' + this.activatedRow.id)
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

    onEditGroupPermission (data) {
      let _self = this
      this.activatedRow = data

      axios.get(this.groupPermissionApiPrefix + '/' + this.activatedRow.id)
        .then(function (res) {
          _self.currentGroupPermission = _.merge({}, _.cloneDeep(shownMenu), res.data.view_access)
        })
    },
  },

  mounted: function () {
    let _self = this

    this.$root.eventHub.$on('editGroupNameEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('editGroupPermissionEvent', this.onEditGroupPermission)
    this.$root.eventHub.$on('deleteGroupEvent', (data) => _self.activatedRow = data)
  },
})