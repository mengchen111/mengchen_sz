import { myTools } from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyVuetable,
    MyDatePicker,
  },
  data: {
    eventHub: new Vue(),
    dateFormat: 'YYYY-MM-DD HH:mm:ss',

    formData: {
      community_id: '',
      start_time: '',
      end_time: '',
    },
    totalConsumedCard: 0,

    validCardConsumedRecordsApi: '/admin/api/community/valid-card-consumed',

    tableUrl: '/admin/api/community/valid-card-consumed?data_type=detail&community_id=', //community_id为空，获取空数据
    tableFields: [
      {
        name: 'id',
        title: '条目id',
      },
      {
        name: 'community_id',
        title: '牌艺馆id',
      },
      {
        name: 'uid',
        title: '玩家id',
      },
      {
        name: 'val',
        title: '消耗数量',
      },
      {
        name: 'time',
        title: '时间',
      },
      {
        name: 'note',
        title: '备注',
      },
    ],
  },
  methods: {
    getValidCardConsumedRecords () {
      let _self = this

      this.tableUrl = this.validCardConsumedRecordsApi + '?data_type=detail'
        + '&' + jQuery.param(this.formData)   //更改tableUrl之后vuetable会自动刷新数据

      axios.get(this.validCardConsumedRecordsApi + '?data_type=summary', {
        params: this.formData,
      })
        .then(function (response) {
          _self.totalConsumedCard = response.data.total_consumed
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  created () {
    this.formData.community_id = myTools.getQueryString('community_id')
    if (this.formData.community_id) {   //从牌艺馆页面跳转过来时要查询一次，直接打开的就不查询
      this.getValidCardConsumedRecords()
    }
  },
})