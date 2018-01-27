<template>
    <div class="table-actions">
        <button class="btn btn-primary btn-block btn-flat"
                @click="manageCommunityAction(rowData)" title="管理" v-if="rowData.status == 1">管理
        </button>
        <button class="btn btn-primary btn-block btn-flat" title="待审核" v-if="rowData.status == 0">待审核
        </button>
        <button class="btn btn-danger btn-block btn-flat" title="待审核" v-if="rowData.status == 2">审核不通过
        </button>
        <!--代理商不允许删除社区-->
        <!--<button class="btn btn-danger btn-flat" data-toggle="modal" data-target="#delete-community-modal"-->
                <!--@click="deleteCommunityAction(rowData)" title="删除">删除-->
        <!--</button>-->
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