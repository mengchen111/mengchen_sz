<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-tasks_player-modal"
                @click="editTaskPlayerAction(rowData)" title="编辑玩家任务">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-tasks_player-modal"
                @click="deleteTasksPlayerAction(rowData)" title="删除玩家任务">删除
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
      editTaskPlayerAction (data) {
        this.$root.eventHub.$emit('editTasksPlayerEvent', data)
      },
      deleteTasksPlayerAction (data) {
        this.$root.eventHub.$emit('deleteTasksPlayerEvent', data)
      },
    },
  }
</script>