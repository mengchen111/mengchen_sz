import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
  },
  data: {
    eventHub: new Vue(),

    tasksPlayerApi: '/admin/api/activities/tasks-player',
    taskTypeMapApi: '/admin/api/activities/task-type-map',
    taskTypeMap: {}, //任务id和名称映射关系
    taskTypeOptions: [],
    taskTypeValue: '',
    editTasksPlayerForm: {},
    addTasksPlayerForm: {},

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
      // {
      //   name: '__component:table-actions',
      //   title: '操作',
      //   titleClass: 'text-center',
      //   dataClass: 'text-center',
      // },
    ],
  },

  methods: {
  },

  created: function () {
  },

  mounted: function () {
  },
})