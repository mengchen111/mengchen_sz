<template>
    <div @click="onClick">
        <div class="">
            <label>创建时间:&nbsp;&nbsp;</label>
            <span>{{rowData.created_at}}</span>
        </div>
        <div class="">
            <label>最后登录时间:&nbsp;&nbsp;</label>
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
    data: function () {
      return {
        coins: null,
      }
    },
    methods: {
      onClick () {
        //console.log('my-detail-row: on-click', event.target)
      },
    },
  }
</script>