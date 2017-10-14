<template>
    <date-picker
            :config='datePickerConf'
            :value="value"
            @input="updateDate($event)"
            :placeholder="placeholder">
    </date-picker>
</template>

<script>
  // Import this component
  import datePicker from 'vue-bootstrap-datetimepicker'

  // Import date picker css
  import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css'

  export default {
    props: {
      value: {
        default: null,
      },
      placeholder: {
        default: '',
      },
      dateFormat: {
        default: 'YYYY-MM-DD HH:mm:ss',
      },
    },

    data () {
      return {
        datePickerConf: {       //datePicker配置项
          locale: 'zh_cn',
          format: this.dateFormat,
        },
      }
    },

    components: {
      datePicker,
    },

    methods: {
      updateDate: function (value) {
        //如果value为空的话不触发事件，不然value.format报错
        return value ? this.$emit('input', value.format(this.dateFormat)) : null
      },
    },
  }
</script>