<template>
    <div class="table-actions">
        <button class="btn btn-primary btn-flat" title="待审核" v-if="rowData.status == 0" @click="auditMemberAction(rowData)"
                data-toggle="modal" data-target="#autdit-community-modal">审核
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
      auditMemberAction (data) {
        this.$root.eventHub.$emit('auditMemberEvent', data)
      },
      deleteCommunityAction (data) {
        this.$root.eventHub.$emit('deleteCommunityEvent', data)
      },
    },
  }
</script>