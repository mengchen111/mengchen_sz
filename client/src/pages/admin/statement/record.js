import '../common.js'
import MyVuetable from '../../../components/MyVuetable.vue'

new Vue({
  el: '#app',
  components: {
    MyVuetable,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},
    uid: null,
    roundData: {},      //战绩流水
    rankingData: {},    //总分排行
    gameFeatures: {},

    recordApi: '/admin/api/statement/records',
    recordInfoApiPrefix: '/admin/api/statement/record-info',
    tableUrl: '/admin/api/statement/records?uid=0',  //初始数据
    tableFields: [
      {
        name: 'rec_id',
        title: '战绩id',
      },
      {
        name: 'room_id',
        title: '房间号',
      },
      {
        name: 'owner_id',
        title: '房主id',
      },
      {
        name: 'game_type',
        title: '游戏类型',
      },
      {
        name: 'time',
        title: '时间',
      },
    ],
  },

  methods: {
    getRecord () {
      let uid = this.uid ? this.uid : 0
      this.tableUrl = this.recordApi + '?uid=' + uid  //更改tableUrl之后vuetable会自动刷新数据
    },

    onCellClicked (data) {
      this.activatedRow = data
      let _self = this

      axios.get(`${this.recordInfoApiPrefix}/${this.activatedRow.rec_id}`)
        .then(function (res) {
          _self.roundData = res.data.rounds
          _self.rankingData = res.data.ranking

          jQuery('#detail-record-modal-button').click() //弹出战绩流水框
        })


    },
  },

  mounted: function () {
    let _self = this

    this.$root.eventHub.$on('detailRecordActionEvent', function (data) {
      _self.activatedRow = data
    })
    this.$root.eventHub.$on('vuetableCellClicked', this.onCellClicked)
    this.$root.eventHub.$on('vuetableDataError', function (data) {
      let err = data.error
      if (err.includes('玩家不存在')) {
        return alert('玩家不存在')
      }
      return alert(err)
    })
  },
})