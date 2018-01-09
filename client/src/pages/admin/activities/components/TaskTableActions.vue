<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-task-modal"
                @click="editTaskAction(rowData)" title="编辑任务">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-task-modal"
                @click="deleteTaskAction(rowData)" title="删除任务">删除
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
      editTaskAction (data) {
        this.$root.eventHub.$emit('editTaskEvent', data)
      },
      deleteTaskAction (data) {
        this.$root.eventHub.$emit('deleteTaskEvent', data)
      },
    },
  }
</script>