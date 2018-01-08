<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-reward-modal"
                @click="editRewardAction(rowData)" title="编辑奖品">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-reward-modal"
                @click="deleteRewardAction(rowData)" title="删除奖品">删除
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
      editRewardAction (data) {
        this.$root.eventHub.$emit('editRewardEvent', data)
      },
      deleteRewardAction (data) {
        this.$root.eventHub.$emit('deleteRewardEvent', data)
      },
    },
  }
</script>