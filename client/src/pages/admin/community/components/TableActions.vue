<template>
    <div class="table-actions">
        <button class="btn btn-primary btn-flat" title="待审核" v-if="rowData.status == 0"
                @click="auditMemberAction(rowData)"
                data-toggle="modal" data-target="#autdit-community-modal">审核

        </button>
        <!--编辑-->
        <button class="btn btn-primary btn-flat" title="编辑" v-if="rowData.status == 1"
                @click="editCommunityAction(rowData)"
                data-toggle="modal" data-target="#edit-community-modal">编辑

        </button>
        <a class="btn btn-default btn-flat" v-if="rowData.status == 1"
           :href="'/admin/community/valid-card?community_id=' + rowData.id"
           title="牌艺馆有效耗卡记录"><i class="glyphicon glyphicon-align-justify"></i>
        </a>
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
      editCommunityAction (data) {
        this.$root.eventHub.$emit('editCommunityEvent', data)
      },
    },
  }
</script>