/**
 * Created by liudian on 9/14/17.
 */

import Vue from 'vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/FilterBar.vue'
import axios from 'axios'

Vue.component('custom-actions', {
  template: `
        <div class="row">
            <button class="btn btn-small btn-danger btn-flat" @click="dismissRoom(rowData)">
                解散房间
            </button>
            <div class="overlay" v-show="loading"><i class="fa fa-refresh fa-spin"></i></div>
        </div>`,
  props: {
    rowData: {
      type: Object,
      required: true
    },
    rowIndex: {
      type: Number
    }
  },
  data: function () {
    return {
      loading: false,     //loading遮罩层
      api: '/admin/api/game/room/friend'
    }
  },
  methods: {
    dismissRoom (rowData) {
      let _self = this
      this.loading = true
      axios.delete(`${_self.api}/${rowData.owner}`)
        .then(function (response) {
          _self.loading = false
          return response.data.error ? alert(response.data.error) : alert(response.data.message)
        })
    }
  }
})

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    FilterBar,
  },
  data: {
    eventHub: new Vue(),     //中心事件处理器
    tableUrl: '/admin/api/game/room/friend',
    tableFields: [
      {
        name: 'id',
        title: '房间ID',
        sortField: 'id'
      },
      {
        name: 'owner',
        title: '房主ID',
        sortField: 'owner'
      },
      {
        name: 'open_id',
        title: '用户账号'
      },
      {
        name: 'create_time',
        title: '创建时间'
      },
      {
        name: '__component:custom-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center'
      },
    ],
    tableSortOrder: [    //默认的排序
      {
        field: 'owner',
        sortField: 'owner',
        direction: 'desc',
      }
    ],
  },
})