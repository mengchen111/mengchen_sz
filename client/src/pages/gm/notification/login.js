/**
 * Created by liudian on 9/15/17.
 */

import Vue from 'vue'
import axios from 'axios'
import DatePicker from '../../../components/DatePicker.vue'
import moment from 'moment'

let app = new Vue({
    el: '#app',
    components: {
        DatePicker,
    },
    data: {
        createFormData: {
            priority: 1,
            interval: null,
            start_at: null,
            end_at: null,
            content: null,
        },
        priorityType: {         //跑马灯公告优先级
            1: '高',
            2: '低'
        },
    },

    methods: {
        sendCreatePost () {
            console.log(this.createFormData)
        },

        test (value) {
            console.log(value);
        }

    }
})
