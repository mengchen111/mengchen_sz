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

    communityDetail: '',  //社区信息数据
    editCommunityForm: {},  //编辑社区的名字和简介
    topupCommunityCardForm: {}, //充值社区房卡

    communityDetailApiPrefix: '/agent/api/community/detail/',
    editCommunityInfoApiPrefix: '/agent/api/community/info/',
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
          _self.getCommunityDetail()
        })
        .catch(function (err) {
          alert(err)
        })
    },

    topupCommunityCard () {
      console.log('充值社区房卡')
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
  },

  created: function () {
    let _self = this
    let communityId = myTools.getQueryString('community')

    if (! communityId) {
      window.location.href = 'list' //如果社区id为空，则直接跳转回社区列表页面
    } else {
      //获取此社区的详细信息
      this.getCommunityDetail()
    }
  },

  mounted: function () {
  },
})