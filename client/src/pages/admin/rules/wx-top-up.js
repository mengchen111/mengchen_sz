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
    prefixApi: '/admin/api/wx-top-up-rules',

    tableUrl: '/admin/api/wx-top-up-rules',
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'remark',
        title: '说明',
      },
      {
        name: 'amount',
        title: '房卡数量',
      },
      {
        name: 'give',
        title: '赠送',
      },
      {
        name: 'first_give',
        title: '首冲赠送',
        callback: 'transFirstGive',
      },
      {
        name: 'price',
        title: '价格',
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
      transFirstGive: function (value) {
        return value + ' %'
      },
    },
  },

  methods: {
    onEditItem (data) {
      this.editedForm = _.cloneDeep(data)
      this.editedForm.amount = data.amount
      this.editedForm.give = data.give
      this.editedForm.first_give = data.first_give
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