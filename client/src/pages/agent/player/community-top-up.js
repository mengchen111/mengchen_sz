import {myTools} from '../index.js'
import MyToastr from '../../../components/MyToastr.vue'
import vSelect from 'vue-select'

new Vue({
  el: '#app',
  components: {
    MyToastr,
    vSelect,
  },
  data: {
    itemType: {
      1: '房卡',
      //2: '金币',  //暂不开放金币
    },
    typeValue: '房卡',
    topUpData: {
      item_type_id: 1,
      community_id: null,
      item_type_amount: '',
      remark: '',
    },
    communityIds: [],
    topUpApi: '/agent/api/community/card/top-up',
    communitiesApi: '/agent/api/communities',  //获取此代理商的所有牌艺馆
  },

  computed: {
    options: function () {
      return _.values(this.itemType)
    },
  },

  watch: {
    typeValue: function (val) {
      this.topUpData.item_type_id = _.findKey(this.itemType, (o) => o === val)
    },
  },

  methods: {
    topUpCommunity () {
      let _self = this
      let toastr = this.$refs.toastr

      if (this.topUpData.item_amount <= 0) {
        return toastr.message('数量错误', 'error')
      }

      myTools.axiosInstance.post(this.topUpApi, this.topUpData)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          //_self.topUpData.item_type_amount = ''
          _self.topUpData.remark = ''
        })
    },
  },

  created () {
    let _self = this

    myTools.axiosInstance.get(this.communitiesApi)
      .then(function (res) {
        _self.communityIds = res.data.community_ids
      })
  },
})