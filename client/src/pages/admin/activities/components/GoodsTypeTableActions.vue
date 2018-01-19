<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-goods-type-modal"
                @click="editGoodsTypeAction(rowData)" title="编辑道具">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-goods-type-modal"
                @click="deleteGoodsTypeAction(rowData)" title="删除道具">删除
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
      editGoodsTypeAction (data) {
        this.$root.eventHub.$emit('editGoodsTypeEvent', data)
      },
      deleteGoodsTypeAction (data) {
        this.$root.eventHub.$emit('deleteGoodsTypeEvent', data)
      },
    },
  }
</script>