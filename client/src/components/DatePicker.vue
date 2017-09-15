<template>
    <date-picker
            :config='datePickerConf'
            :value="value"
            v-on:input="updateDate($event)"
            :placeholder="placeholder">
    </date-picker>
</template>

<script>
    // Import this component
    import datePicker from 'vue-bootstrap-datetimepicker'

    // Import date picker css
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css'

    import moment from 'moment'
    import 'moment/locale/zh-cn'    //引入本地语言

    export default {
        props: {
            value: {
                default: moment().format(),
            },
            placeholder: {
                default: '',
            }
        },

        data () {
            return {
                datePickerConf: {       //datePicker配置项
                    locale: moment().local('zh_cn'),
                    format: 'YYYY-MM-DD HH:mm:ss',
                },
            }
        },

        components: {
            datePicker
        },

        methods: {
            updateDate: function (value) {
                this.$emit('input', value.format('YYYY-MM-DD HH:mm:ss'))
            }
        }
    }
</script>