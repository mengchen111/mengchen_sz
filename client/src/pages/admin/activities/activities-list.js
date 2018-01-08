import { myTools } from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'
import MyToastr from '../../../components/MyToastr.vue'
import ActivitiesTableActions from './components/ActivitiesTableActions.vue'
import vSelect from 'vue-select'

Vue.component('table-actions', ActivitiesTableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyDatePicker,
    MyToastr,
    vSelect,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    dateFormat: 'YYYY-MM-DD HH:mm:ss',

    activitiesStateMap: [
      '关闭', '开启'  //0为关闭，1为开启
    ],
    activitiesRewardMap: {}, //活动奖品id和奖品名的映射
    activitiesRewardOptions: [],  //活动奖品选项 for vSelect
    activitiesRewardValue: [],    //活动奖品多选框，选定的值
    activitiesStateValue: '开启', //活动状态的默认状态
    editActivitiesForm: {},
    addActivitiesForm: {},
    activitiesApi: '/admin/api/activities/list',
    activitiesRewardMapApi: '/admin/api/activities/reward-map',

    tableUrl: '/admin/api/activities/list',
    tableFields: [
      {
        name: 'aid',
        title: '活动id',
      },
      {
        name: 'name',
        title: '名称',
      },
      {
        name: 'open',
        title: '状态',
        callback: 'transState',
      },
      {
        name: 'open_time',
        title: '开始时间',
      },
      {
        name: 'end_time',
        title: '结束时间',
      },
      {
        name: 'reward_model',
        title: '奖品',
        callback: 'transReward',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],

    callbacks: {
      transState: function (value) {
        if (value) {
          return '开启'
        } else {
          return '关闭'
        }
      },
      transReward: function (value) {
        let rewards = ''
        value.forEach(function (item) {
          rewards += item.name + ','
        })
        return rewards
      },
    },
  },

  methods: {
    onEditActivities (data) {
      this.activitiesStateValue = this.activitiesStateMap[data.open]
      this.activitiesRewardValue = _.map(data.reward_model, 'name')
      this.editActivitiesForm.aid = data.aid
      this.editActivitiesForm.name = data.name
      this.editActivitiesForm.open_time = data.open_time
      this.editActivitiesForm.end_time = data.end_time
    },

    //创建activities时，重置vselect的默认选项
    onCreateActivities () {
      this.activitiesStateValue = '开启'
      this.activitiesRewardValue = []
    },

    addActivities () {
      let _self = this
      let toastr = this.$refs.toastr

      //开启状态
      this.addActivitiesForm.open = _.findIndex(this.activitiesStateMap, v => v === this.activitiesStateValue)
      //将reward的名字组合成逗号分割的id的形式（后端存储在数据库中是以这种形式存储的）
      this.addActivitiesForm.reward = this.transRewardValue2id(this.activitiesRewardValue)

      myTools.axiosInstance.post(this.activitiesApi, this.addActivitiesForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    editActivities () {
      let _self = this
      let toastr = this.$refs.toastr

      //开启状态
      this.editActivitiesForm.open = _.findIndex(this.activitiesStateMap, v => v === this.activitiesStateValue)
      //将reward的名字组合成逗号分割的id的形式（后端存储在数据库中是以这种形式存储的）
      this.editActivitiesForm.reward = this.transRewardValue2id(this.activitiesRewardValue)

      myTools.axiosInstance.put(this.activitiesApi, this.editActivitiesForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    transRewardValue2id (rewardValue) {
      let _self = this
      let reward = ''

      _.forEach(rewardValue, function (v) {
        reward += _.findKey(_self.activitiesRewardMap, (mapValue) => mapValue === v) + ','
      })

      return _.trim(reward, ',')
    },

    deleteActivities () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.activitiesApi + '/' + this.activatedRow.aid)
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

    //获取活动奖品map
    myTools.axiosInstance.get(this.activitiesRewardMapApi)
      .then(function (res) {
        myTools.msgResolver(res, toastr)
        _self.activitiesRewardMap = res.data
        _self.activitiesRewardOptions = _.values(res.data)
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editActivitiesEvent', this.onEditActivities)
    this.$root.eventHub.$on('deleteActivitiesEvent', (data) => _self.activatedRow = data)
  },
})