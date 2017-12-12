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
    stockApplyApi: '/admin/api/stock',
    itemType: {
      1: '房卡',
      //2: '金币',
    },
    typeValue: '房卡',
    stockApplyData: {
      item_id: 1,
      amount: null,
      remark: null,
    },
  },

  computed: {
    options: function () {
      return _.values(this.itemType)
    },
  },

  watch: {
    typeValue: function (value) {
      this.stockApplyData.item_id = _.findKey(this.itemType, (o) => o === value)
    },
  },

  methods: {
    stockApply () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.stockApplyApi, this.stockApplyData)
        .then(function (res) {
          myTools.msgResolver(res, toastr)

          _self.stockApplyData.amount = null
          _self.stockApplyData.remark = null
        })
        .catch(function (err) {
          alert(err)
        })
    },
  },
})