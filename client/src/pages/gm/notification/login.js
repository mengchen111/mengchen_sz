/**
 * Created by liudian on 9/15/17.
 */

import Vue from 'vue'
import DatePicker from '../../../components/DatePicker.vue'
import MyVuetable from '../../../components/MyVuetable.vue'
import TableActions from '../../../components/gm/notification/TableActions.vue'
import DetailRow from '../../../components/gm/notification/DetailRow.vue'

Vue.component('table-actions', TableActions);
Vue.component('detail-row', DetailRow);

let app = new Vue({
    el: '#app',
    components: {
        DatePicker,
        MyVuetable,
    },
    data: {
        eventHub: new Vue(),
        formData: {
            order: null,
            title: null,
            content: null,
            pop_frequency: 1,
            start_at: null,
            end_at: null,
        },
        popFrequency: {         //登录公告弹出频率
            1: '每日首次登录',
            2: '每次登录'
        },
        createApi: '/admin/api/game/notification/login',

        //vuetable props
        tableUrl: '/admin/api/game/notification/login',
        detailRowComponent: 'detail-row',
        tableFields: [
            {
                name: 'id',
                title: '公告ID',
                sortField: 'id',
            },
            {
                name: 'order',
                title: '序号',
            },
            {
                name: 'title',
                title: '标题'
            },
            {
                name: 'content',
                title: '公告内容',
            },
            {
                name: 'pop_frequency',
                title: '弹出频率',
                callback: 'transPopFrequency'
            },
            {
                name: 'start_at',
                title: '开始时间',
                sortField: 'start_at',
            },
            {
                name: 'end_at',
                title: '结束时间',
                sortField: 'end_at',
            },
            {
                name: 'switch',
                title: '开启状态',
                sortField: 'switch',
                callback: 'transSwitch'
            },
            {
                name: 'sync_state',
                title: '同步状态',
                sortField: 'sync_state',
                callback: 'transSyncState'
            },
            {
                name: '__component:table-actions',
                title: '操作',
                titleClass: 'text-center',
                dataClass: 'text-center',
            },
        ],
        callbacks: {
            transSwitch (value) {
                let switchType = {           //公告开启状态
                    1: '开启',
                    2: '关闭'
                };
                return switchType[value];
            },
            transSyncState (value) {
                let syncState = {            //公告同步状态
                    1: '未同步',
                        2: '同步中',
                        3: '同步成功',
                        4: '同步失败',
                };
                let state = syncState[value];

                switch (value)
                {
                    case 1:
                        return `<span><b>${state}</b></span>`;
                    case 2:
                        return `<span style="color: #00c0ef;"><b>${state}</b></span>`;
                    case 3:
                        return `<span style="color: #00a65a;"><b>${state}</b></span>`;
                    case 4:
                        return `<span style="color: #dd4b39;"><b>${state}</b></span>`;
                    default:
                        return true;
                }
            },
            transPopFrequency (value) {
                let popFrequency = {         //登录公告弹出频率
                    1: '每日首次登录',
                    2: '每日登录'
                };
                return popFrequency[value];
            }
        }
    },

    methods: {
        createNotification () {
            let _self = this;

            axios({
                method: 'POST',
                url: this.createApi,
                data: this.formData,
                validateStatus: function (status) {
                    return status == 200 || status == 422;
                }
            })
                .then(function (response) {
                    if (response.status === 422) {
                        return alert(JSON.stringify(response.data))
                    } else {
                        if (response.data.error) {
                            return alert(response.data.error)
                        }

                        alert(response.data.message);

                        //清空表单数据
                        for (var index of Object.keys(_self.formData)) {
                            _self.formData[index] = null;
                        }

                        //添加成功，刷新表格
                        _self.$root.eventHub.$emit('vuetableRefresh');

                        return true;
                    }
                })
                .catch(function (err) {
                    alert(err);
                    console.log(err);
                });
        }
    },

    mounted: function () {

    }
})
