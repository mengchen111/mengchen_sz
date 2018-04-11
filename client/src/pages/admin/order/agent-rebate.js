import  {myTools} from '../index.js'
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
    activatedRow: {},
    uid: null,

    searchType: '0',
    statisticsData: {},
    statisticsApi: '/admin/api/rebates/user/statistics/',
    userPrefixApi: '/admin/api/rebates/user',
    tableUrl: '/admin/api/rebates/user',  //初始数据
    tableFields: [
      {
        name: 'id',
        title: 'ID',
      },
      {
        name: 'user.name',
        title: '昵称',
      },
      {
        name: 'total_amount',
        title: '当月充值金额',
      },
      {
        name: 'children.name',
        title: '提供的代理商',
      },
      {
        name: 'children.group.name',
        title: '代理级别',
      },
      {
        name: 'rebate_at',
        title: '年月份',
      },
      {
        name: 'rebate_price',
        title: '返利金额',
      },
      {
        name: 'rule.rate',
        title: '返利比例（单位 %）',
      },
    ],
  },

  methods: {
    getRecord () {
      let _self = this
      let uid = this.uid ? this.uid : 0
      this.tableUrl = this.userPrefixApi + '/' + uid  //更改tableUrl之后vuetable会自动刷新数据
      let url = this.statisticsApi + uid

      //获取实时数据
      myTools.axiosInstance.get(url)
        .then(function (res) {
          _self.statisticsData = res.data
        })
    },


  },

  // mounted: function () {
  //   let _self = this
  //   let toastr = this.$refs.toastr
  //
  //   this.$root.eventHub.$on('detailRecordActionEvent', function (data) {
  //     _self.activatedRow = data
  //   })
  //   this.$root.eventHub.$on('MyVuetable:cellClicked', this.onCellClicked)
  //   this.$root.eventHub.$on('MyVuetable:error', function (data) {
  //     let err = data.error
  //     if (err.includes('玩家不存在')) {
  //       return toastr.message('玩家不存在', 'error')
  //     }
  //     return toastr.message(err, 'error')
  //   })
  // },
})