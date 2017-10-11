<template>
    <div class="table-actions">
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#top-up-modal"
                @click="topUpAgentAction(rowData)" title="充卡"><i class="glyphicon glyphicon-credit-card"></i>
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#agent-info-modal"
                @click="editInfoAction(rowData)" title="更新代理商信息"><i class="glyphicon glyphicon-edit"></i>
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#agent-password-modal"
                @click="changePasswordAction(rowData)" title="更改密码"><i class="glyphicon glyphicon-pencil"></i>
        </button>
        <button class="btn btn-default btn-flat" data-toggle="modal" data-target="#delete-agent-modal"
                @click="deleteAgentAction(rowData)" title="删除代理商"><i class="glyphicon glyphicon-trash"></i>
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
      topUpAgentAction (data) {
        this.$root.eventHub.$emit('topUpAgentEvent', data)
      },
      editInfoAction (data) {
        this.$root.eventHub.$emit('editInfoEvent', data)
      },
      changePasswordAction (data) {
        this.$root.eventHub.$emit('changeAgentPasswordEvent', data)
      },
      deleteAgentAction (data) {
        this.$root.eventHub.$emit('deleteAgentEvent', data)
      },
    },
  }
</script>