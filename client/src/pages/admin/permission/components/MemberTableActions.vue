<template>
    <div class="table-actions" v-if="rowData.id != 1">
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#edit-member-modal"
                @click="editMemberAction(rowData)" title="编辑">编辑
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#delete-member-modal"
                @click="deleteMemberAction(rowData)" title="删除">删除
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
      editMemberAction (data) {
        this.$root.eventHub.$emit('editMemberEvent', data)
      },
      deleteMemberAction (data) {
        this.$root.eventHub.$emit('deleteMemberEvent', data)
      },
    },
  }
</script>