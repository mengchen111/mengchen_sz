<template>
    <div @click="onClick">
        <div class="">
            <label>任务开放时间段:&nbsp;&nbsp;</label>
            <span>{{rowData.mission_time}}</span>
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
        //console.log('my-detail-row: on-click', event.target)
      },
    },
  }
</script>