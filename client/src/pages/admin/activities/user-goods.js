import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import vSelect from 'vue-select'
import UserGoodsTableActions from './components/UserGoodsTableActions.vue'

Vue.component('table-actions', UserGoodsTableActions)

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

    userGoodsApi: '/admin/api/activities/user-goods',
    goodsTypeMapApi: '/admin/api/activities/goods-type-map',
    goodsTypeMap: {}, //道具id和名称映射关系
    goodsTypeOptions: [],
    goodsTypeName: '',
    editUserGoodsForm: {},
    addUserGoodsForm: {},

    tableUrl: '/admin/api/activities/user-goods',
    tableFields: [
      {
        name: 'user_id',
        title: '玩家id',
      },
      {
        name: 'player.nickname',
        title: '玩家昵称',
      },
      {
        name: 'goods_type_model.goods_name',
        title: '道具物品',
      },
      {
        name: 'goods_cnt',
        title: '物品数量',
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
    onEditUserGoods (data) {
      this.goodsTypeName = data.goods_type_model.goods_name
      this.editUserGoodsForm.user_id = data.user_id
      this.editUserGoodsForm.goods_cnt = data.goods_cnt
    },

    editUserGoods () {
      let _self = this
      let toastr = this.$refs.toastr

      if (! this.goodsTypeName) {
        return toastr.message('奖励不能为空', 'error')
      }

      this.editUserGoodsForm.goods_id = _.findKey(this.goodsTypeMap, (v) => v === this.goodsTypeName)

      myTools.axiosInstance.put(this.userGoodsApi, this.editUserGoodsForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteUserGoods () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.userGoodsApi, {
        'params': this.activatedRow,
      })
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    onAddUserGoods () {
      this.goodsTypeName = ''
    },

    addUserGood () {
      let _self = this
      let toastr = this.$refs.toastr

      if (! this.goodsTypeName) {
        return toastr.message('奖励不能为空', 'error')
      }

      this.addUserGoodsForm.goods_id = _.findKey(this.goodsTypeMap, (v) => v === this.goodsTypeName)

      myTools.axiosInstance.post(this.userGoodsApi, this.addUserGoodsForm)
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
    let _self = this
    let toastr = this.$refs.toastr

    //获取goods type map
    myTools.axiosInstance.get(this.goodsTypeMapApi)
      .then(function (res) {
        myTools.msgResolver(res, toastr)
        _self.goodsTypeMap = res.data
        _self.goodsTypeOptions = _.values(res.data)
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editUserGoodsEvent', this.onEditUserGoods)
    this.$root.eventHub.$on('deleteUserGoodsEvent', (data) => _self.activatedRow = data)
  },
})