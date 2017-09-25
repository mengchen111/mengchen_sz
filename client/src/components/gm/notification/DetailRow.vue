<template>
    <div @click="onClick">
        <div class="">
            <label>失败原因:&nbsp;&nbsp;</label>
            <span>{{rowData.failed_description}}</span>
        </div>
        <div class="">
            <label>创建时间:&nbsp;&nbsp;</label>
            <span>{{rowData.created_at}}</span>
        </div>
        <div class="">
            <label>更新时间:&nbsp;&nbsp;</label>
            <span>{{rowData.updated_at}}</span>
        </div>
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
      onClick () {
        //console.log('detail-row: on-click', event.target)
      },
    },
  }
</script>