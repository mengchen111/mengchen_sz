<template>
    <div class="table-actions" v-if="rowData.order_status === 2">
        <button class="btn btn-primary btn-flat" @click="orderPaymentAction(rowData)" title="支付">
            支付
        </button>
        <button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#order-cancellation-modal"
                @click="orderCancellationAction(rowData)" title="关闭">
            关闭
        </button>
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
      orderPaymentAction (data) {
        this.$root.eventHub.$emit('orderPaymentEvent', data)
      },
      orderCancellationAction (data) {
        this.$root.eventHub.$emit('orderCancellationEvent', data)
      },
    },
  }
</script>