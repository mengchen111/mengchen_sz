import '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import FilterBar from '../../../components/MyFilterBar.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),

    tableUrl: '/admin/api/activities/log-activity-reward',
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'pid',
        title: '奖品id',
      },
      {
        name: 'activity_reward.name',
        title: '奖品名称',
      },
      {
        name: 'uid',
        title: '玩家id',
      },
      {
        name: 'player.nickname',
        title: '玩家昵称',
      },
      {
        name: 'time',
        title: '时间',
      },
    ],
  },

  methods: {
    //
  },

  created: function () {
    //
  },

  mounted: function () {
    //
  },
})