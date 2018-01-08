<template>
    <div class="table-actions">
        <button class="btn btn-flat btn-primary" data-toggle="modal" data-target="#edit-activities-modal"
                @click="editActivitiesAction(rowData)" title="编辑活动">编辑
        </button>
        <button class="btn btn-flat btn-danger" data-toggle="modal" data-target="#delete-activities-modal"
                @click="deleteActivitiesAction(rowData)" title="删除活动">删除
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
      editActivitiesAction (data) {
        this.$root.eventHub.$emit('editActivitiesEvent', data)
      },
      deleteActivitiesAction (data) {
        this.$root.eventHub.$emit('deleteActivitiesEvent', data)
      },
    },
  }
</script>