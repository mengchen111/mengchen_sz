<template>
    <toastr ref="toastr"></toastr>
</template>

<script>
  import Toastr from 'vue-toastr'
  import 'vue-toastr/src/vue-toastr.less'

  export default {
    name: 'my-toastr',
    components: {
      Toastr,
    },
    props: {
      options: {
        type: Object,
        default: () => ({
          defaultTimeout: 3000,             //消息框显示的时间
          defaultProgressBar: false,        //是否显示进度条
          defaultType: 'success',           //默认的消息类型
          defaultPosition: 'toast-top-right', //消息位置
          defaultCloseOnHover: false,        //鼠标悬浮一直显示
        }),
      },
    },

    methods: {
      setToastrOptions (options) {
        for (let [key, value] of _.entries(options)) {
          this.$refs.toastr[key] = value
        }
      },
      message (msg, type = this.options.defaultType, title = '') {
        this.$refs.toastr.Add({
          title: title,
          msg: msg,
          type: type,
        })
      },
    },

    mounted: function () {
      this.setToastrOptions(this.options)
    },
  }
</script>