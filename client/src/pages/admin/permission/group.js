import '../common.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import TableActions from './components/GroupTableActions.vue'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    groupListApi: '/admin/api/group',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
      },
      {
        name: 'name',
        title: '组名',
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
    editGroupName () {
      console.log('edit group name')
    },

    deleteGroup () {
      console.log('delete group')
    },
  },

  created: function () {
  },

  mounted: function () {
    let _self = this

    this.$root.eventHub.$on('editGroupNameEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('editGroupPermissionEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteGroupEvent', (data) => _self.activatedRow = data)
  },
})