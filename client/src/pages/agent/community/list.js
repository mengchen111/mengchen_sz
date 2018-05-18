import {myTools} from '../index.js'
import MyVuetable from '../../../components/MyVuetable.vue'
import MyToastr from '../../../components/MyToastr.vue'
import TableActions from './components/TableActions.vue'
import vSelect from 'vue-select'
import {Checkbox, Radio} from 'vue-checkbox-radio'

Vue.component('table-actions', TableActions)

new Vue({
  el: '#app',
  components: {
    MyVuetable,
    MyToastr,
    vSelect,
    Radio,
    Checkbox,
  },
  data: {
    eventHub: new Vue(),
    activatedRow: {},

    statusOptions: [
      '待审核', '已审核', '审核不通过', '全部',
    ],
    statusDefaultValue: '全部',
    addCommunityForm: {},
    communityApi: '/agent/api/community',
    gameTypes: {},  //牌艺馆关联的游戏包，可玩的游戏类型
    gameTypeRules: {}, //每种游戏类型可用的选项
    createGameRulesForm: {},  //创建默认玩法时的绑定数据

    gameTypeRulesApiPrefix: '/agent/api/community/game-type/rules',
    modifyGameRuleTemplateApiPrefix: '/agent/api/community/game-type/rule',
    gameRuleTemplateApi: '/agent/api/community/game-type/rule',
    tableUrl: '/agent/api/community?status=1',  //默认显示已审核
    tableFields: [
      {
        name: 'id',
        title: 'id',
      },
      {
        name: 'owner_player_id',
        title: '玩家id',
      },
      {
        name: 'name',
        title: '名称',
      },
      {
        name: 'game_group_name',
        title: '游戏包',
      },
      // {
      //   name: 'info',
      //   title: '简介',
      // },
      // {
      //   name: 'card_stock',
      //   title: '房卡',
      // },
      {
        name: 'members_count',
        title: '成员数',
      },
      // {
      //   name: 'created_at',
      //   title: '创建时间',
      // },
      {
        name: '__component:table-actions',
        title: '操作',
        titleClass: 'text-center',
        dataClass: 'text-center',
      },
    ],
  },

  methods: {
    onManageCommunity (data) {
      //跳转到管理页面上去
      window.location.href = 'manage?community=' + data.id
    },

    addCommunity () {
      let _self = this
      let toastr = this.$refs.toastr

      myTools.axiosInstance.post(this.communityApi, this.addCommunityForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
          _self.addCommunityForm.owner_player_id = ''
          _self.addCommunityForm.name = ''
          _self.addCommunityForm.info = ''
        })
        .catch(function (err) {
          alert(err)
        })
    },

    deleteCommunity () {
      let _self = this
      let toastr = this.$refs.toastr
      let api = `${_self.communityApi}/${_self.activatedRow.id}`

      myTools.axiosInstance.delete(api)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
          _self.$root.eventHub.$emit('MyVuetable:refresh')
        })
        .catch(function (err) {
          alert(err)
        })
    },

    onSelectChange (value) {
      let status = _.findIndex(this.statusOptions, (v) => v === value)
      this.tableUrl = '/agent/api/community?status=' + status
    },

    onEditCommunityGameOption (data) {
      let _self = this
      let toastr = this.$refs.toastr
      this.activatedRow = _.cloneDeep(data)   //当前被选中的行的数据赋值

      if (0 === data.game_group) {
        return toastr.message('此牌艺馆未关联到游戏包', 'error')
      } else {
        this.gameTypes = data.game_group_game_types
        myTools.axiosInstance.get(this.gameTypeRulesApiPrefix + '/' + data.id)
          .then(function (res) {
            _self.gameTypeRules = res.data
            jQuery('#edit-game-option-modal-button').click()
          })
      }
    },

    createGameRulesTemplate (gameTypeId) {
      let toastr = this.$refs.toastr

      //console.log(gameTypeId, this.createGameRulesForm)
      this.createGameRulesForm.game_type = Number(gameTypeId) //表单数据加上游戏类型id
      myTools.axiosInstance.post(this.modifyGameRuleTemplateApiPrefix + '/' + this.activatedRow.id, this.createGameRulesForm)
        .then(function (res) {
          myTools.msgResolver(res, toastr)
        })
    },

    gameTypeTabClick (gameTypeId) {
      let _self = this

      //console.log(gameTypeId)
      if (this.gameTypeRules[gameTypeId].hasOwnProperty('wanfa')) {
        this.createGameRulesForm = {
          'wanfa': [],
        } //清空数据，防止污染
      } else {
        this.createGameRulesForm = {}
      }

      //获取游戏模版，合并到createGameRulesForm上
      myTools.axiosInstance.get(this.gameRuleTemplateApi, {
        'params': {
          'game_type': gameTypeId,
          'community_id': this.activatedRow.id,
        },
      })
        .then(function (res) {
          //返回的结果为对象才表示配置了默认规则，结果为空则默认游戏规则未配置
          if (typeof res.data === 'object') {
            _self.createGameRulesForm = _.assign(_self.createGameRulesForm, res.data.rule)
            //console.log(res.data, _self.createGameRulesForm)
          }
        })
    },
  },

  created: function () {
    //
  },

  mounted: function () {
    let _self = this
    this.$root.eventHub.$on('manageCommunityEvent', this.onManageCommunity)
    this.$root.eventHub.$on('deleteCommunityEvent', (data) => _self.activatedRow = data)
    this.$root.eventHub.$on('editCommunityGameOptionEvent', this.onEditCommunityGameOption)
  },
})