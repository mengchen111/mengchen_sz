<template>
    <div class="table-actions">
        <button class="btn btn-primary btn-flat" data-toggle="modal" data-target="#edit-item-modal"
                @click="editPriceAction(rowData)" title="编辑价格">
            编辑价格
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
      editPriceAction (data) {
        this.$root.eventHub.$emit('editPriceEvent', data)
      },
    },
  }
</script>