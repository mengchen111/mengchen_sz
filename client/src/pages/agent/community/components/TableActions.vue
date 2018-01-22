<template>
    <div class="table-actions">
        <button class="btn btn-primary btn-flat" data-toggle="modal" data-target="#manage-community-modal"
                @click="manageCommunityAction(rowData)" title="管理">管理
        </button>
        <button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#delete-community-modal"
                @click="deleteCommunityAction(rowData)" title="删除">删除
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
      manageCommunityAction (data) {
        this.$root.eventHub.$emit('manageCommunityEvent', data)
      },
      deleteCommunityAction (data) {
        this.$root.eventHub.$emit('deleteCommunityEvent', data)
      },
    },
  }
</script>