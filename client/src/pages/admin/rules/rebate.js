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
    dateFormat: 'YYYY-MM-DD HH:mm:ss',

    editedForm: {},
    createdForm: {},
    prefixApi: '/admin/api/rebate-rules',

    tableUrl: '/admin/api/rebate-rules',
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'price',
        title: '累计金额',
      },
      {
        name: 'rate',
        title: '返利比例',
        callback: 'transRate',
      },
      {
        name: 'remark',
        title: '说明',
      },
      {
        name: 'created_at',
        title: '创建时间',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],

    callbacks: {
      transRate: function (value) {
        return value + ' %'
      },
    },
  },

  methods: {
    onEditItem (data) {
      this.editedForm = _.cloneDeep(data)
      this.editedForm.rate = data.rate
      this.editedForm.price = data.price
      this.editedForm.remark = data.remark
    },

    store () {
      let _self = this
      let toastr = this.$refs.toastr

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