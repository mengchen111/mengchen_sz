/**
 * Created by liudian on 9/15/17.
 */

import Vue from 'vue'
import axios from 'axios'
import moment from 'moment'
import DatePicker from '../../../components/DatePicker.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import FilterBar from '../../../components/FilterBar.vue'

let app = new Vue({
    el: '#app',
    components: {
        DatePicker,
        MyVuetable,
        FilterBar,
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
        eventHub: new Vue(),

        //vuetable props
        tableUrl: '/admin/api/game/player',
        tableFields: [
            {
                name: 'rid',
                title: '玩家ID',
                sortField: 'rid',
            },
            {
                name: 'nick',
                title: '玩家昵称',
            },
            {
                name: 'card.count',
                title: '房卡数量'
            },
            {
                name: 'gold',
                title: '金币数量',
                sortField: 'gold',
            },
            {
                name: 'last_login_time',
                title: '最后登录时间',
            },
            {
                name: 'last_offline_time',
                title: '最后离线时间'
            },
        ],
        tableSortOrder: [    //默认的排序
            {
                field: 'rid',
                sortField: 'rid',
                direction: 'desc',
            }
        ],
        tableTrackBy: 'rid',
        detailRowComponent: 'detail-row'
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
