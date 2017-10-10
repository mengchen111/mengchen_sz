<template>
    <div class="table-actions">
        <button class="btn btn-success btn-flat" @click="applyApproveAction(rowData)">通过</button>
        <button class="btn btn-danger btn-flat" @click="applyDeclineAction(rowData)">拒绝</button>
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
      applyApproveAction(data) {
        this.$root.eventHub.$emit('applyApproveEvent', data)
      },
      applyDeclineAction(data) {
        this.$root.eventHub.$emit('applyDeclineEvent', data)
      },
    },
  }
</script>