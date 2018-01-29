import {myTools} from '../index.js'
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

    communityDetail: {
      application_data: {}, //提前给出key，防止前端报错
    },  //社区信息数据
    communityRooms: {}, //社区房间信息
    editCommunityForm: {},  //编辑社区的名字和简介
    topupCommunityCardForm: {
      item_type_id: 1,  //房卡类型id
    }, //充值社区房卡
    searchPlayerForm: {
      player_id: '',
    },   //查询玩家
    searchPlayerData: {},
    kickingOutMemberId: '',   //即将被踢出的成员id

    communityDetailApiPrefix: '/agent/api/community/detail/',
    editCommunityInfoApiPrefix: '/agent/api/community/info/',
    topUpCommunityCardApiPrefix: '/agent/api/community/card/',
    searchPlayerApi: '/api/game/player',
    invitePlayerApi: '/agent/api/community/member/invitation', //邀请玩家入群
    kickOutPlayerApi: '/agent/api/community/member/kick-out',
    approveApplicationApiPrefix: '/agent/api/community/member/approval-application/',
    declineApplicationApiPrefix: '/agent/api/community/member/decline-application/',
    communityRoomApiPrefix: '/agent/api/community/room/',
  },

  methods: {
    onEditCommunity () {
      this.editCommunityForm.name = this.communityDetail.name
      this.editCommunityForm.info = this.communityDetail.info
    },

    editCommunity () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.put(this.editCommunityInfoApiPrefix + this.communityDetail.id, this.editCommunityForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.getCommunityDetail()  //刷新此页面的社区数据
        })
        .catch(function (err) {
          alert(err)
        })
    },

    topupCommunityCard () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.topUpCommunityCardApiPrefix + this.communityDetail.id, this.topupCommunityCardForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.getCommunityDetail()  //刷新此页面的社区数据
        })
        .catch(function (err) {
          alert(err)
        })
    },

    //查找玩家
    searchPlayer () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.get(this.searchPlayerApi, {
        params: this.searchPlayerForm,
      }).then(function (res) {
        if (_.has(res.data, 'error')) {
          myTools.msgResolver(res, toastr)
          _self.searchPlayerForm.player_id = '' //清空搜索框
        } else {
          _self.searchPlayerData = res.data
          _self.searchPlayerForm.player_id = '' //清空搜索框
          jQuery('#search-player-pop_up-modal-button').click() //弹出玩家查找结果框
        }
      }).catch(function (err) {
        alert(err)
      })
    },

    //邀请玩家加入社区
    inviteMember () {
      let toastr = this.$refs.toastr
      let invitePlayerForm = {
        player_id: this.searchPlayerData.id,
        community_id: this.communityDetail.id,
      }

      myTools.axiosInstance.post(this.invitePlayerApi, invitePlayerForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
        }).catch(function (err) {
          alert(err)
        })
    },

    //踢出社区
    kickOutMember () {
      let _self = this
      let toastr = this.$refs.toastr
      let kickOutPlayerForm = {
        player_id: this.kickingOutMemberId,
        community_id: this.communityDetail.id,
      }

      myTools.axiosInstance.put(this.kickOutPlayerApi, kickOutPlayerForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.getCommunityDetail()  //重新获取数据
        }).catch(function (err) {
          alert(err)
        })
    },

    //同意入群申请
    approveApplication (applicationId) {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.put(this.approveApplicationApiPrefix + applicationId)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.getCommunityDetail()  //重新获取数据
        }).catch(function (err) {
          alert(err)
        })
    },

    //拒绝入群申请
    declineApplication (applicationId) {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.put(this.declineApplicationApiPrefix + applicationId)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.getCommunityDetail()  //重新获取数据
        }).catch(function (err) {
          alert(err)
        })
    },

    getCommunityDetail () {
      let _self = this
      let communityId = myTools.getQueryString('community')

      myTools.axiosInstance.get(this.communityDetailApiPrefix + communityId)
        .then(function (res) {
          _self.communityDetail = res.data

          _self.editCommunityForm.name = _self.communityDetail.name
          _self.editCommunityForm.info = _self.communityDetail.info
        })
    },

    getCommunityRooms () {
      let _self = this
      let communityId = this.communityDetail.id

      myTools.axiosInstance.get(this.communityRoomApiPrefix + communityId)
        .then(function (res) {
          _self.communityRooms = res.data
        })
    },
  },

  created: function () {
    let communityId = myTools.getQueryString('community')

    if (! communityId) {
      window.location.href = 'list' //如果社区id为空，则直接跳转回社区列表页面
    } else {
      //获取此社区的详细信息
      this.getCommunityDetail()
    }
  },

  mounted: function () {
    //
  },
})