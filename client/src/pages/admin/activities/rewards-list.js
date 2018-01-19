import { myTools } from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import RewardTableActions from './components/RewardTableActions.vue'
import vSelect from 'vue-select'

Vue.component('table-actions', RewardTableActions)

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

    addRewardForm: {},
    editRewardForm: {},
    goodsTypeMap: {},
    goodsTypeOptions: [],
    goodsTypeValue: null,
    rewardApi: '/admin/api/activities/reward',
    goodsTypeMapApi: '/admin/api/activities/goods-type-map',

    tableUrl: '/admin/api/activities/reward',
    tableFields: [
      {
        name: 'pid',
        title: '奖品id',
      },
      {
        name: 'name',
        title: '名称',
      },
      {
        name: 'img',
        title: '奖品图标',
      },
      {
        name: 'show_text',
        title: '展示文字',
      },
      {
        name: 'total_inventory',  //负数为无限
        title: '总库存',
        callback: 'transTotalInventory',
      },
      {
        name: 'probability',
        title: '概率',
      },
      {
        name: 'single_limit',
        title: '单人限制',
      },
      {
        name: 'expend',
        title: '已耗数量',
      },
      {
        name: 'goods_type_model.goods_name',
        title: '道具类型',
      },
      {
        name: 'goods_count',
        title: '道具数量',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    callbacks: {
      transTotalInventory: function (value) {
        if (value < 0) {
          return '无限'
        } else {
          return value
        }
      },
    },
  },

  methods: {
    onAddReward () {
      this.goodsTypeValue = ''   //重置奖品道具的值
    },

    addReward () {
      let _self = this
      let toastr = this.$refs.toastr

      this.addRewardForm.goods_type = _.findKey(this.goodsTypeMap, v => v === this.goodsTypeValue)

      myTools.axiosInstance.post(this.rewardApi, this.addRewardForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    onEditReward (data) {
      this.activatedRow = data
      this.activatedRow.goodsTypeValue = data.goods_type_model.goods_name
    },

    editReward () {
      let _self = this
      let toastr = this.$refs.toastr

      this.activatedRow.goods_type = _.findKey(this.goodsTypeMap, v => v === this.activatedRow.goodsTypeValue)

      myTools.axiosInstance.put(this.rewardApi, this.activatedRow)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteReward () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.rewardApi + '/' + this.activatedRow.pid)
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

    //获取活动奖品道具map
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
    this.$root.eventHub.$on('editRewardEvent', this.onEditReward)
    this.$root.eventHub.$on('deleteRewardEvent', (data) => _self.activatedRow = data)
  },
})