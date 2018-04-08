<template>
    <div @click="onClick">
        <div>
            <label>内部订单号:&nbsp;&nbsp;</label>
            <span>{{rowData.out_trade_no}}</span>
        </div>
        <div>
            <label>微信预支付id:&nbsp;&nbsp;</label>
            <span>{{rowData.prepay_id}}</span>
        </div>
        <div>
            <label>支付者openid:&nbsp;&nbsp;</label>
            <span>{{rowData.openid}}</span>
        </div>
        <div>
            <label>微信交易id:&nbsp;&nbsp;</label>
            <span>{{rowData.transaction_id}}</span>
        </div>
        <div>
            <label>订单错误消息:&nbsp;&nbsp;</label>
            <span class="text-danger">{{rowData.order_err_msg}}</span>
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
        //console.log('detail-row: on-click', event.target)
      },
    },
  }
</script>