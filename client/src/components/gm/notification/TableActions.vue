<template>
    <div class="table-actions">
        <button class="btn btn-success btn-flat" @click="enableNotificationAction(rowData)">
            开启
        </button>
        <button class="btn btn-warning btn-flat" @click="disableNotificationAction(rowData)">
            关闭
        </button>
        <button class="btn btn-primary btn-flat" data-toggle="modal" data-target="#edit-notification-modal"
                @click="editNotificationAction(rowData)">修改
        </button>
        <button class="btn btn-danger btn-flat"
                @click="deleteNotificationAction(rowData)">删除
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
      enableNotificationAction (data) {
        return this.$root.eventHub.$emit('enableNotificationEvent', data)
      },
      disableNotificationAction (data) {
        return this.$root.eventHub.$emit('disableNotificationEvent', data)
      },
      editNotificationAction (data) {
        return this.$root.eventHub.$emit('editNotificationEvent', data)
      },
      deleteNotificationAction (data) {
        return this.$root.eventHub.$emit('deleteNotificationEvent', data)
      },
    },
  }
</script>