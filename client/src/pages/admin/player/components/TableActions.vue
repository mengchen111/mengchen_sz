<template>
    <div class="table-actions">
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#player-top-up-modal"
                @click="topUpPlayerAction(rowData)" title="充值">
            充值
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
      topUpPlayerAction (data) {
        this.$root.eventHub.$emit('topUpPlayerEvent', data)
      },
    },
  }
</script>