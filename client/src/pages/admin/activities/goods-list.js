import { myTools } from '../index.js'
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

    goodsApi: '/admin/api/activities/goods',

    tableUrl: '/admin/api/activities/goods',
    tableFields: [
      {
        name: 'goods_id',
        title: '道具id',
      },
      {
        name: 'goods_name',
        title: '道具名称',
      },
      // {
      //   name: '__component:table-actions',
      //   title: '操作',
      //   titleClass: 'text-center',
      //   dataClass: 'text-center',
      // },
    ],
  },

  methods: {
    //
  },

  created: function () {
    //
  },

  mounted: function () {
    console.log('goods list')
  },
})