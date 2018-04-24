import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import vSelect from 'vue-select'
import PlayerTaskTableActions from './components/PlayerTaskTableActions.vue'

Vue.component('table-actions', PlayerTaskTableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    vSelect,
  },
  data: {
    eventHub: new Vue(),

    tasksPlayerApi: '/admin/api/activities/tasks-player',
    resetTasksPlayerApi: '/admin/api/activities/tasks-player/reset',
    taskMapApi: '/admin/api/activities/task-map',
    taskMap: {}, //任务id和名称映射关系
    taskMapOptions: [],
    taskValue: '',
    isCompletedOptions: [
      '否', '是',
    ],
    isCompletedValue: '否',
    editTasksPlayerForm: {},
    addTasksPlayerForm: {},
    count: 0,

    tableUrl: '/admin/api/activities/tasks-player',
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'task.name',
        title: '任务',
      },
      {
        name: 'uid',
        title: '玩家id',
      },
      {
        name: 'process',
        title: '进度',
      },
      {
        name: 'is_completed',
        title: '是否完成',
      },
      {
        name: 'count',
        title: '剩余可完成次数',
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
    onEditTasksPlayer (data) {
      this.taskValue = data.task.name
      this.editTasksPlayerForm.uid = data.uid
      this.editTasksPlayerForm.task_id = data.task.id //编辑玩家任务，不允许编辑任务，不然就等于是创建新的了
      this.editTasksPlayerForm.process = data.process
      this.isCompletedValue = this.isCompletedOptions[data.is_completed]
      this.count = data.count
    },

    editTasksPlayer () {
      let _self = this
      let toastr = this.$refs.toastr

      //this.editTasksPlayerForm.task_id = _.findKey(this.taskMap, (v) => v === this.taskValue)
      this.editTasksPlayerForm.is_completed = _.findIndex(this.isCompletedOptions, (v) => v === this.isCompletedValue)
      this.editTasksPlayerForm.count = this.count

      myTools.axiosInstance.put(this.tasksPlayerApi, this.editTasksPlayerForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteTasksPlayer () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.delete(this.tasksPlayerApi, {
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

    onAddTasksPlayer () {
      this.taskValue = ''
      this.addTasksPlayerForm.uid = ''
      this.addTasksPlayerForm.process = 0
      this.isCompletedValue = '否'
      this.count = 0
    },

    addTasksPlayer () {
      let _self = this
      let toastr = this.$refs.toastr

      if (!this.taskValue) {
        return toastr.message('任务不能为空', 'error')
      }

      this.addTasksPlayerForm.task_id = _.findKey(this.taskMap, (v) => v === this.taskValue)
      this.addTasksPlayerForm.is_completed = _.findIndex(this.isCompletedOptions, (v) => v === this.isCompletedValue)

      myTools.axiosInstance.post(this.tasksPlayerApi, this.addTasksPlayerForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    resetTasksPlayer () {
      let _self = this
      let toastr = this.$refs.toastr

      if (!this.taskValue) {
        return toastr.message('请选择一个任务重置，不能为空', 'error')
      }

      let params = {
        id: _.findKey(this.taskMap, (v) => v === this.taskValue),
      }

      myTools.axiosInstance.put(this.resetTasksPlayerApi, params)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.taskValue = ''  //调用完成之后重置选项的值
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
    myTools.axiosInstance.get(this.taskMapApi)
      .then(function (res) {
        myTools.msgResolver(res, toastr)
        _self.taskMap = res.data
        _self.taskMapOptions = _.values(res.data)
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('editTasksPlayerEvent', this.onEditTasksPlayer)
    this.$root.eventHub.$on('deleteTasksPlayerEvent', (data) => _self.activatedRow = data)
  },
})