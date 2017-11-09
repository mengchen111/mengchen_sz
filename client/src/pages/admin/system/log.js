/**
 * Created by liudian on 9/20/17.
 */

import '../common.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/MyFilterBar.vue'

Vue.component('detail-row', {
  template: `
        <div @click="onClick">
          <div class="">
            <label>浏览器版本:&nbsp;&nbsp;</label>
            <span>{{rowData.user_agent}}</span>
          </div>
          <div class="">
            <label>操作数据:&nbsp;&nbsp;</label>
            <span>{{rowData.data}}</span>
          </div>
       </div>`,
  props: {
    rowData: {
      type: Object,
      required: true,
    },
    rowIndex: {
      type: Number,
    },
  },
  methods: {
    onClick () {
      //console.log('my-detail-row: on-click', event.target)
    },
  },
})

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),
    tableUrl: '/admin/api/system/log',
    tableFields: [
      {
        name: 'id',
        title: 'ID',
        sortField: 'id',
      },
      {
        name: 'user.account',
        title: '用户账号',
        sortField: 'user_id',
      },
      {
        name: 'uri',
        title: 'URI路径',
        sortField: 'uri',
      },
      {
        name: 'method',
        title: '操作方法',
        sortField: 'method',
      },
      {
        name: 'description',
        title: '描述',
      },
      {
        name: 'created_at',
        title: '时间',
      },
    ],
    detailRowComponent: 'detail-row',
  },
})