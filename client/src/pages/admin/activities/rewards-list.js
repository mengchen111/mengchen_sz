import { myTools } from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import RewardTableActions from './components/RewardTableActions.vue'

Vue.component('table-actions', RewardTableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    addRewardForm: {},
    rewardApi: '/admin/api/activities/reward',

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
    addReward () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.rewardApi, this.addRewardForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    editReward () {
      let _self = this
      let toastr = this.$refs.toastr

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
    //
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editRewardEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('deleteRewardEvent', (data) => _self.activatedRow = data)
  },
})