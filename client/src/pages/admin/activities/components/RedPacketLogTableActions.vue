<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-status-modal"
                @click="editStatusAction(rowData)" title="编辑状态" v-show="rowData.send_status === 2">编辑状态
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
      editStatusAction (data) {
        this.$root.eventHub.$emit('editStatusEvent', data)
      },
    },
  }
</script>