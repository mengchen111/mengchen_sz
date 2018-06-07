<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-modal"
                @click="editAction(rowData)" title="编辑">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-modal"
                @click="deleteAction(rowData)" title="删除">删除
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
      editAction (data) {
        this.$root.eventHub.$emit('editItemEvent', data)
      },
      deleteAction (data) {
        this.$root.eventHub.$emit('deleteItemEvent', data)
      },
    },
  }
</script>