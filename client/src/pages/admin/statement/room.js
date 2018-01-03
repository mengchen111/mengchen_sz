import {myTools} from'../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyDatePicker from '../../../components/MyDatePicker.vue'
import vSelect from 'vue-select'

new Vue({
  el: '#app',
  components: {
    MyToastr,
    MyDatePicker,
    vSelect,
  },
  data: {
    eventHub: new Vue(),

    dateFormat: 'YYYY-MM-DD',
    roomTypeMap: {},
    roomTypeOptions: null,
    roomTypeValue: '全部',
    searchFormData: {
      'date': moment().format('YYYY-MM-DD'),
      'game_kind': '',
    },
    roomTypeMapApi: '/api/game/room/type-map',
    roomStatementApi: '/admin/api/statement/room',
    roomStatementData: {},
  },

  watch: {
    roomTypeMap: function (val) {
      this.roomTypeOptions = _.values(val)
      this.roomTypeOptions.unshift('全部')
    },
    roomTypeValue: function (val) {
      if (val === '全部') {
        return this.searchFormData.game_kind = ''
      }

      return this.searchFormData.game_kind = _.findKey(this.roomTypeMap, (n) => n === val)
    },
  },

  methods: {
    getRoomStatement () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.get(this.roomStatementApi, {
        params: this.searchFormData,
      })
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.roomStatementData = res.data
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },

  created: function () {
    let _self = this

    myTools.axiosInstance.get(this.roomTypeMapApi)
      .then(function (res) {
        _self.roomTypeMap = res.data
      })
      .catch(function (err) {
        alert(err)
      })
  },

  mounted: function () {
    let _self = this
    let toastr = this.$refs.toastr

    myTools.axiosInstance.get(this.roomStatementApi, {
      params: this.searchFormData,
    })
      .then(function (res) {
        myTools.msgResolver(res, toastr)
        _self.roomStatementData = res.data
      })
      .catch(function (err) {
        alert(err)
      })
  },
})