<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-user_goods-modal"
                @click="editUserGoodsAction(rowData)" title="编辑玩家物品">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-user_goods-modal"
                @click="deleteUserGoodsAction(rowData)" title="删除玩家物品">删除
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
      editUserGoodsAction (data) {
        this.$root.eventHub.$emit('editUserGoodsEvent', data)
      },
      deleteUserGoodsAction (data) {
        this.$root.eventHub.$emit('deleteUserGoodsEvent', data)
      },
    },
  }
</script>