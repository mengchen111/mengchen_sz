import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'

Vue.component('table-actions', TableActions)
new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    MyDatePicker,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    dateFormat: 'YYYY-MM-DD HH:mm:ss',
    startTime: {
      date: moment().format('YYYY-MM-DD HH:mm:ss'),
    },
    endTime: {
      date: moment().format('YYYY-MM-DD HH:mm:ss'),
    },
    editedForm: {},
    createdForm: {},
    statusData:{
      1:'开启',
      2:'关闭',
    },
    syncData:{
      1:'已同步',
      2:'未同步',
    },
    prefixApi: '/admin/api/gm/marquee',
    tableUrl: '/admin/api/gm/marquee',
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'level',
        title: '公告优先级',
      },
      {
        name: 'content',
        title: '公告内容',
      },
      {
        name: 'stime',
        title: '开始时间',
      },
      {
        name: 'etime',
        title: '结束时间',
      },
      {
        name: 'diff_time',
        title: '间隔时间',
      },
      {
        name: 'status',
        title: '开启状态',
        callback: 'transStatus',
      },
      {
        name: 'sync',
        title: '同步状态',
        callback: 'transSync',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    callbacks: {
      transStatus: function (value) {
        return this.$root.statusData[value]
      },
      transSync: function (value) {
        return this.$root.syncData[value]
      },
    },
  },
  methods: {
    onEditItem (data) {
      this.editedForm = _.cloneDeep(data)
      this.startTime.date = data.stime
      this.endTime.date = data.etime
    },
    store () {
      let _self = this
      let toastr = this.$refs.toastr
      this.createdForm.stime = this.startTime.date
      this.createdForm.etime = this.endTime.date

      myTools.axiosInstance.post(this.prefixApi, this.createdForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },
    update () {
      let _self = this
      let toastr = this.$refs.toastr
      this.editedForm.stime = this.startTime.date
      this.editedForm.etime = this.endTime.date

      let url = `${_self.prefixApi}/${_self.editedForm.id}`
      myTools.axiosInstance.put(url, this.editedForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },
    deleteItem () {
      let _self = this
      let toastr = this.$refs.toastr
      myTools.axiosInstance.delete(this.prefixApi + '/' + this.activatedRow.id)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },
  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editItemEvent', this.onEditItem)
    this.$root.eventHub.$on('deleteItemEvent', (data) => _self.activatedRow = data)
  },
})