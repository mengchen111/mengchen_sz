import {myTools} from '../index.js'
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
    roomTypes: {},   //每种房间对应的可用选项
    currentPageData: null,  //当前页面的数据
    activeRoomType: '惠州',  //默认的打开的tab
    createRoomFormData: {},

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
      this.createRoomFormData = {}

      if (this.roomTypes[room]['wanfa']) {
        this.createRoomFormData.wanfa = this.roomTypes[room]['wanfa']['options']
      }
    },

    createRoom (room) {
      let _self = this
      let toastr = this.$refs.toastr
      this.createRoomFormData.room = _.findKey(this.rooms, (value) => value === room)   //房间类型id
      this.createRoomFormData.players = 4 //玩家数量

      this.httpClient.post(this.roomCreateApi, this.createRoomFormData)
        .then(function (res) {
          _self.msgResolver(res, toastr)
        })
        .catch(function (err) {
          alert(err)
        })
    },

    setActiveRoomWanfa () {
      this.createRoomFormData.wanfa = this.roomTypes[this.activeRoomType]['wanfa']['options']
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
        _self.roomTypes = res.data.room_types
        _self.setActiveRoomWanfa()     //填充默认选中的tab的玩法菜单
      })
      .catch(function (err) {
        toastr.message(err, 'error')
      })

    this.$root.eventHub.$on('MyPagination:data', (data) => _self.currentPageData = data)
    this.$root.eventHub.$on('MyPagination:error', (err) => toastr.message(err, 'error'))
  },
})