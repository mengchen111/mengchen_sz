import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TaskDetailRow from './components/TaskDetailRow.vue'
import TaskTableActions from './components/TaskTableActions.vue'
import vSelect from 'vue-select'

Vue.component('detail-row', TaskDetailRow)
Vue.component('table-actions', TaskTableActions)

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

    taskApi: '/admin/api/activities/task',
    taskTypeMapApi: '/admin/api/activities/task-type-map',
    taskGoodsTypeMapApi: '/admin/api/activities/goods-type-map',
    editTaskForm: {},
    taskTypeMap: {},      //任务类型的id和comment的映射关系
    taskTypeComment: '',  //任务类型描述
    taskTypeOptions: [],  //可选的任务类型
    ifDailyOptions: ['否', '是'],
    dailyValue: '',
    taskGoodsTypeMap: {}, //任务奖励的id和名称映射关系
    taskGoodsTypeOptions: [],
    taskGoodsTypeName: '',
    taskGoodsCount: 0,
    addTaskForm: {},
    count: 1,

    tableUrl: '/admin/api/activities/task',
    tableFields: [
      {
        name: 'id',
        title: '任务id',
      },
      {
        name: 'name',
        title: '名称',
      },
      {
        name: 'type_model.comment',
        title: '任务类型',
      },
      {
        name: 'begin_time',
        title: '开始时间',
      },
      {
        name: 'end_time',
        title: '结束时间',
      },
      {
        name: 'daily',
        title: '是否日常',
        callback: 'transDailyValue',
      },
      {
        name: 'target',
        title: '目标次数',
      },
      {
        name: 'reward_good',
        title: '奖励',
      },
      {
        name: 'reward_count',
        title: '奖励数量',
      },
      {
        name: 'link',
        title: '链接',
      },
      {
        name: 'count',
        title: '可完成次数',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    callbacks: {
      transDailyValue (value) {
        if (value) {
          return '是'
        } else {
          return '否'
        }
      },
    },
  },

  methods: {
    onEditTask (data) {
      this.taskTypeComment = this.taskTypeMap[data.type]
      this.dailyValue = this.ifDailyOptions[data.daily] //将是否日常选项的值改为中文

      this.editTaskForm.id = data.id
      this.editTaskForm.name = data.name
      this.editTaskForm.begin_time = data.begin_time
      this.editTaskForm.end_time = data.end_time
      this.editTaskForm.mission_time = data.mission_time
      this.editTaskForm.target = data.target
      this.editTaskForm.link = data.link
      this.count = data.count

      this.taskGoodsTypeName = data.reward_good
      this.taskGoodsCount = data.reward_count
    },

    editTask () {
      let _self = this
      let toastr = this.$refs.toastr

      if (!this.taskGoodsTypeName) {
        return toastr.message('奖励不能为空', 'error')
      }

      this.editTaskForm.reward = this.formatRewardValue(this.taskGoodsTypeName, this.taskGoodsCount)
      this.editTaskForm.type = _.findKey(this.taskTypeMap, (v) => v === this.taskTypeComment)
      this.editTaskForm.daily = _.findKey(this.ifDailyOptions, (v) => v === this.dailyValue)
      this.editTaskForm.count = this.count

      myTools.axiosInstance.put(this.taskApi, this.editTaskForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    onAddTask () {
      this.taskTypeComment = ''
      this.taskGoodsTypeName = ''
      this.taskGoodsCount = 1
      this.dailyValue = '是'
      this.addTaskForm.mission_time = '00:00:00-23:59:59'
      this.count = 1

    },

    addTask () {
      let _self = this
      let toastr = this.$refs.toastr

      if (!this.taskGoodsTypeName) {
        return toastr.message('奖励不能为空', 'error')
      }

      this.addTaskForm.reward = this.formatRewardValue(this.taskGoodsTypeName, this.taskGoodsCount)
      this.addTaskForm.type = _.findKey(this.taskTypeMap, (v) => v === this.taskTypeComment)
      this.addTaskForm.daily = _.findKey(this.ifDailyOptions, (v) => v === this.dailyValue)
      this.addTaskForm.count = this.count

      myTools.axiosInstance.post(this.taskApi, this.addTaskForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    //将任务奖励和奖励次数格式化成 ID_COUNT的格式
    formatRewardValue (goods, count) {
      let goodsId = _.findKey(this.taskGoodsTypeMap, (v) => v === goods)
      return goodsId + '_' + count
    },

    deleteTask () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.taskApi + '/' + this.activatedRow.id)
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

    //获取task type map
    myTools.axiosInstance.get(this.taskTypeMapApi)
      .then(function (res) {
        myTools.msgResolver(res, toastr)
        _self.taskTypeMap = res.data
        _self.taskTypeOptions = _.values(res.data)
      })
      .catch(function (err) {
        alert(err)
      })

    //获取task goods type map
    myTools.axiosInstance.get(this.taskGoodsTypeMapApi)
      .then(function (res) {
        myTools.msgResolver(res, toastr)
        _self.taskGoodsTypeMap = res.data
        _self.taskGoodsTypeOptions = _.values(res.data)
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editTaskEvent', this.onEditTask)
    this.$root.eventHub.$on('deleteTaskEvent', (data) => _self.activatedRow = data)
  },
})