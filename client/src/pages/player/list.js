import Vue from 'vue'
import MyVuetable from '../../components/MyVuetable.vue'
import TableActions from '../../components/player/TableActions.vue'
import FilterBar from '../../components/FilterBar.vue'
import axios from 'axios'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    tableUrl: '/admin/api/game/player',
    tableTrackBy: 'uid',
    tableFields: [
      {
        name: 'uid',
        title: '玩家ID',
      },
      {
        name: 'nickname',
        title: '玩家昵称',
      },
      {
        name: 'ycoins',
        title: '房卡数量',
      },
      {
        name: 'ypoints',
        title: '金币数量',
      },
      {
        name: 'state',
        title: '账号状态',
      },
      {
        name: 'create_time',
        title: '创建时间',
      },
      {
        name: 'last_time',
        title: '最后登录时间',
      },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
    tableSortOrder: [      //默认的排序
      {
        field: 'uid',
        sortField: 'uid',
        direction: 'desc',
      },
    ],
  },

  mounted: function () {
    this.$root.eventHub.$on('vuetableDataError', (data) => alert(data.error))
  },
})
