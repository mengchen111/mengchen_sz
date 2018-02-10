<template>
    <div @click="onClick">
        <div>
            <label>游戏库log_redbag_id:&nbsp;&nbsp;</label>
            <span>{{rowData.log_redbag_id}}</span>
        </div>
        <div>
            <label>unionid:&nbsp;&nbsp;</label>
            <span>{{rowData.unionid}}</span>
        </div>
        <div>
            <label>openid:&nbsp;&nbsp;</label>
            <span>{{rowData.re_openid}}</span>
        </div>
        <div>
            <label>mch_billno:&nbsp;&nbsp;</label>
            <span>{{rowData.mch_billno}}</span>
        </div>
        <div>
            <label>备注:&nbsp;&nbsp;</label>
            <span>{{rowData.remark}}</span>
        </div>
        <div>
            <label>error_message:&nbsp;&nbsp;</label>
            <span class="text-red">{{rowData.error_message}}</span>
        </div>
    </div>
</template>

<script>
  export default {
    props: {
      rowData: {
        type: Object,
        required: true,
      },
      rowIndex: {
        type: Number,
      },
    },
    methods: {
      onClick () {
        //console.log('my-detail-row: on-click', event.target)
      },
    },
  }
</script>