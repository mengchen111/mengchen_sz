<template>
    <div class="table-actions">
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#top-up-modal"
                @click="topUpChildAction(rowData)" title="充值"><i class="glyphicon glyphicon-credit-card"></i>
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#agent-info-modal"
                @click="editInfoAction(rowData)" title="更新子代理商信息"><i class="glyphicon glyphicon-edit"></i>
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#delete-agent-modal"
                @click="deleteAgentAction(rowData)" title="删除子代理商"><i class="glyphicon glyphicon-trash"></i>
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
      topUpChildAction (data) {
        this.$root.eventHub.$emit('topUpChildEvent', data)
      },
      editInfoAction (data) {
        this.$root.eventHub.$emit('editInfoEvent', data)
      },
      deleteAgentAction (data) {
        this.$root.eventHub.$emit('deleteAgentEvent', data)
      },
    },
  }
</script>