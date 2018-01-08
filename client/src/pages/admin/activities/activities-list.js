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
    activitiesForm: {
      state_value: '开启',
    },
    activitiesApi: '/admin/api/activities/list',

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
        name: 'reward',
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
      this.activitiesForm.aid = data.aid
      this.activitiesForm.name = data.name
      this.activitiesForm.state_value = this.activitiesStateMap[data.open]
      this.activitiesForm.open_time = data.open_time
      this.activitiesForm.end_time = data.end_time
      this.activitiesForm.reward = 'rewards'
    },

    editActivities () {
      let _self = this
      let toastr = this.$refs.toastr

      //开启状态
      this.activitiesForm.open = _.findIndex(this.activitiesStateMap, v => v === this.activitiesForm.state_value)

      myTools.axiosInstance.put(this.activitiesApi, this.activitiesForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteActivities () {
      let _self = this
      let toastr = this.$refs.toastr

      console.log(this.activatedRow)

      myTools.axiosInstance.delete(this.activitiesApi, this.activatedRow)
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
    this.$root.eventHub.$on('editActivitiesEvent', this.onEditActivities)
    this.$root.eventHub.$on('deleteActivitiesEvent', (data) => _self.activatedRow = data)
  },
})