<template>
    <div class="table-actions" v-if="rowData.id != 1">
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#edit-group-name-modal"
                @click="editGroupNameAction(rowData)" title="更改组名">更改组名
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#edit-group-permission-modal"
                @click="editGroupPermissionAction(rowData)" title="更改权限">更改权限
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#delete-group-modal"
                @click="deleteGroupAction(rowData)" title="删除组">删除组
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
      editGroupNameAction (data) {
        this.$root.eventHub.$emit('editGroupNameEvent', data)
      },
      editGroupPermissionAction (data) {
        this.$root.eventHub.$emit('editGroupPermissionEvent', data)
      },
      deleteGroupAction (data) {
        this.$root.eventHub.$emit('deleteGroupEvent', data)
      },
    },
  }
</script>