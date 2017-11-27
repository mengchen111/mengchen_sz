import { myTools } from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import MyPagination from '../../../components/MyPagination.vue'
import {Checkbox, Radio} from 'vue-checkbox-radio'

new Vue({
  el: '#app',
  components: {
    MyToastr,
    Checkbox,
    Radio,
    MyPagination,
  },
  data: {
    eventHub: new Vue(),
    httpClient: myTools.axiosInstance,
    msgResolver: myTools.msgResolver,
    rooms: {},      //可创建的房间
    roomType: {},   //每种房间对应的可用选项
    currentPageData: null,  //当前页面的数据
    activeRoomType: '惠州庄',  //默认的打开的tab
    createRoomFormData: {
      'room': null,
      'rounds': null,
      'wanfa': [],
      'gui_pai': {},
      'ma_pai': null,
    },
    guiPaiData: {
      '花牌类型': null,
    },

    roomTypeApi: '/admin/api/gm/room/type',  //房间类型接口
    roomCreateApi: '/admin/api/gm/room',     //房间创建接口
    getOpenRoomApi: '/admin/api/gm/room/open',
    getRoomHistoryApi: '/admin/api/gm/room/history',
    paginationUrl: null,
  },

  methods: {
    displayOpenRoom () {
      this.paginationUrl = this.getOpenRoomApi
    },

    displayRoomHistory () {
      this.paginationUrl = this.getRoomHistoryApi
    },

    refreshOpenRoomTable () {
      this.$root.eventHub.$emit('MyPagination:changePage', 1)
    },

    refreshClosedRoomTable () {
      this.$root.eventHub.$emit('MyPagination:changePage', 1)
    },

    //玩法默认选中
    tabClick (room) {
      this.createRoomFormData.wanfa = this.roomType[room]['wanfa']
    },

    createRoom (room) {
      let _self = this
      let toastr = this.$refs.toastr
      this.createRoomFormData.room = room

      //鬼牌的选项的值传递到表单数据上
      for (let [type, value] of _.entries(this.guiPaiData)) {
        if (value !== null) {
          this.createRoomFormData.gui_pai[type] = value
        }
      }

      this.httpClient.post(this.roomCreateApi, this.createRoomFormData)
        .then(function (res) {
          _self.msgResolver(res, toastr)
        })
        .catch(function (err) {
          alert(err)
        })
    },

    setActiveRoomWanfa () {
      this.createRoomFormData.wanfa = this.roomType[this.activeRoomType]['wanfa']
    },
  },

  created () {
    this.paginationUrl = this.getOpenRoomApi
  },

  mounted () {
    let _self = this
    let toastr = this.$refs.toastr

    this.httpClient.get(this.roomTypeApi)
      .then(function (res) {
        _self.rooms = res.data.rooms
        _self.roomType = res.data.room_type
        _self.setActiveRoomWanfa()     //填充默认选中的tab的玩法菜单
      })
      .catch(function (err) {
        toastr.message(err, 'error')
      })

    this.$root.eventHub.$on('MyPagination:data', (data) => _self.currentPageData = data)
    this.$root.eventHub.$on('MyPagination:error', (err) => toastr.message(err, 'error'))
  },
})