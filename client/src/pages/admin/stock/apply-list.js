import Vue from 'vue'
import axios from 'axios'
import FilterBar from '../../../components/FilterBar.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import TableActions from './components/TableActions.vue'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    FilterBar,
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    approvalApiPrefix: '/admin/api/stock/approval',
    declineApiPrefix: '/admin/api/stock/decline',

    tableUrl: '/admin/api/stock/list',
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
        name: '__component:table-actions',
        title: '审批',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
  },

  methods: {
    doApplyApprove (data) {
      let _self = this
      let url = `${_self.approvalApiPrefix}/${data.id}`
      axios.post(url)
        .then(function (response) {
          response.data.error ? alert(response.data.error) : alert(response.data.message)
          _self.$root.eventHub.$emit('vuetableRefresh')
        })
    },
    doApplyDecline (data) {
      let _self = this
      let url = `${_self.declineApiPrefix}/${data.id}`
      axios.post(url)
        .then(function (response) {
          response.data.error ? alert(response.data.error) : alert(response.data.message)
          _self.$root.eventHub.$emit('vuetableRefresh')
        })
    },
  },

  mounted: function () {
    this.$root.eventHub.$on('applyApproveEvent', this.doApplyApprove)
    this.$root.eventHub.$on('applyDeclineEvent', this.doApplyDecline)
  },
})