import '../index.js'
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
    roundData: {},      //战绩流水
    rankingData: {},    //总分排行
    roomRules: {},      //房间玩法
    rulesMap: {         //房间玩法显示信息
      'room_type': '房间类型',
      'rounds': '局数',
      'players': '玩家数量',
      'wanfa': '玩法',
      'hua_pai': '花牌',
      'gui_pai': '鬼牌',
      'ma_pai': '马牌',
      'di_fen': '底分',
      'qing_hun': '清混',
      'score_limit': '分数封顶',
    },
    searchType: '0',

    recordApi: '/admin/api/gm/records',
    recordInfoApiPrefix: '/admin/api/gm/record-info',
    tableUrl: '/admin/api/gm/records?uid=0',  //初始数据
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
        title: '房主/玩家 id',
      },
      {
        name: 'community_id',
        title: '牌艺馆id',
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
      let type = this.searchType
      this.tableUrl = this.recordApi + '?uid=' + uid + '&type=' + type //更改tableUrl之后vuetable会自动刷新数据
    },

    onCellClicked (data) {
      this.activatedRow = data
      let _self = this

      axios.get(`${this.recordInfoApiPrefix}/${this.activatedRow.rec_id}`)
        .then(function (res) {
          _self.roundData = res.data.rounds
          _self.rankingData = res.data.ranking
          _self.roomRules = _.mapValues(res.data.rules, (value) => _.trim(value, ','))

          jQuery('#detail-record-modal-button').click() //弹出战绩流水框
        })
    },
  },

  mounted: function () {
    let _self = this
    let toastr = this.$refs.toastr

    this.$root.eventHub.$on('detailRecordActionEvent', function (data) {
      _self.activatedRow = data
    })
    this.$root.eventHub.$on('MyVuetable:cellClicked', this.onCellClicked)
    this.$root.eventHub.$on('MyVuetable:error', function (data) {
      let err = data.error
      if (err.includes('玩家不存在')) {
        return toastr.message('玩家不存在', 'error')
      }
      return toastr.message(err, 'error')
    })
  },
})