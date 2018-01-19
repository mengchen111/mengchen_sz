import { myTools } from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import GoodsTypeTableActions from './components/GoodsTypeTableActions.vue'

Vue.component('table-actions', GoodsTypeTableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    goodsTypeApi: '/admin/api/activities/goods-type',
    addGoodsTypeForm: {},

    tableUrl: '/admin/api/activities/goods-type',
    tableFields: [
      {
        name: 'goods_id',
        title: '道具id',
      },
      {
        name: 'goods_name',
        title: '道具名称',
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
    addGoodsType () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.goodsTypeApi, this.addGoodsTypeForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    editGoodsType () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.put(this.goodsTypeApi, this.activatedRow)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteGoodsType () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.goodsTypeApi + '/' + this.activatedRow.goods_id)
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
    //
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editGoodsTypeEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteGoodsTypeEvent', (data) => _self.activatedRow = data)
  },
})