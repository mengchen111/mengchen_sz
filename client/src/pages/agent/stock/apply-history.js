import Vue from 'vue'
import MyVuetable from '../../../components/MyVuetable.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    tableUrl: '/agent/api/stock/history',
    tableFields: [
      {
        name: 'created_at',
        title: '申请时间',
        sortField: 'id',
      },
      {
        name: 'applicant.account',
        title: '申请人',
      },
      {
        name: 'item.name',
        title: '道具类型',
        sortField: 'item_id',
      },
      {
        name: 'amount',
        title: '数量',
        sortField: 'amount',
      },
      {
        name: 'remark',
        title: '备注',
      },
      {
        name: 'approver.account',
        title: '审批人',
      },
      {
        name: 'updated_at',
        title: '审批时间',
        sortField: 'updated_at',
      },
      {
        name: 'state',
        title: '审核状态',
        //dataClass: 'btn btn-block btn-success',
        callback: 'transState',
        sortField: 'state',
      },
    ],
    callbacks: {
      transState (value) {
        switch (value) {
          case 1:
            return '<button class="btn btn-block btn-primary btn-flat">待审核</button>'
          case 2:
            return '<button class="btn btn-block btn-success btn-flat">通过</button>'
          case 3:
            return '<button class="btn btn-block btn-danger btn-flat">拒绝</button>'
        }
      },
    },
  },
})