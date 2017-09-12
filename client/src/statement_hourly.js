/**
 * Created by liudian on 9/12/17.
 */

import Vue from 'vue';
import MyVuetable from './component/MyVuetable.vue'
import Vuetable from 'vuetable-2';
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination.vue'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo.vue'

let tableTemplate = `
<div>

                        <!--<div class="pagination pull-left">
                            <vuetable-pagination-info ref="paginationInfoTop"
                            ></vuetable-pagination-info>
                        </div>
                        <vuetable-pagination ref="paginationTop"
                                             :css="css.pagination"
                                             @vuetable-pagination:change-page="onChangePage"
                        ></vuetable-pagination>-->

                        <vuetable ref="vuetable"
                                  :api-url="vuetableUrl"
                                  :fields="tableFields"
                                  :sort-order="sortOrder"
                                  :css="css.table"
                                  pagination-path=""
                                  :per-page="15"
                                  :append-params="moreParams"
                                  @vuetable:pagination-data="onPaginationData"
                                  track-by="rid"
                                  @vuetable:cell-clicked="onCellClicked"
                        ></vuetable>

                        <div class="pagination pull-left">
                            <vuetable-pagination-info ref="paginationInfo"
                            ></vuetable-pagination-info>
                        </div>
                        <vuetable-pagination ref="pagination"
                                             :css="css.pagination"
                                             @vuetable-pagination:change-page="onChangePage"
                        ></vuetable-pagination>
                   </div>
`;

let data = {
    vuetableUrl: '/admin/api/player',

    activatedRow: {},       //待编辑的行

    topUpData: {
        type: {
            1: '房卡',
            2: '金币'
        },
        typeId: 1,
        amount: null,
    },

    onlineState: ['离线', '在线'],

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
            name: 'online',
            title: '在线状态',
            sortField: 'online',
            callback: 'getOnlineState'
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

    sortOrder: [    //默认的排序
        {
            field: 'rid',
            sortField: 'rid',
            direction: 'desc',
        }
    ],

    css: {
        table: {
            tableClass: 'table table-striped table-bordered',
            ascendingIcon: 'glyphicon glyphicon-chevron-up',
            descendingIcon: 'glyphicon glyphicon-chevron-down',
            handleIcon: 'glyphicon glyphicon-menu-hamburger',
            renderIcon: function (classes, options) {
                return `<span class="${classes.join(' ')}"></span>`
            }
        },
        pagination: {
            wrapperClass: "pagination pull-right",
            activeClass: "btn-primary",
            disabledClass: "disabled",
            pageClass: "btn btn-border",
            linkClass: "btn btn-border",
            icons: {
                first: "",
                prev: "",
                next: "",
                last: ""
            }
        }
    },

    moreParams: {},
};

new Vue({
    el: '#app',
    data: {
        hello: 'aaa',
    },
    components: {
        'my-vuetable': {
            template: tableTemplate,
            components: {
                Vuetable,
                VuetablePagination,
                VuetablePaginationInfo
            },
            data: function () {
                return data;
            },
            methods: {
                    onPaginationData (paginationData) {
                        //this.$refs.paginationTop.setPaginationData(paginationData)
                        //this.$refs.paginationInfoTop.setPaginationData(paginationData)

                        this.$refs.pagination.setPaginationData(paginationData);
                        this.$refs.paginationInfo.setPaginationData(paginationData);
                    },
                    onChangePage (page) {
                        this.$refs.vuetable.changePage(page);
                    },
                    onCellClicked (data, field, event) {
                        //console.log('cellClicked: ', field.name, data.email)
                        this.$refs.vuetable.toggleDetailRow(data.rid);
                    },

                    getOnlineState (state) {
                        return this.onlineState[state];
                    },

                    topUpPlayer () {
                        var _self = this;

                        axios({
                            method: 'POST',
                            url: `/admin/api/top-up/player/${_self.activatedRow.rid}/${_self.topUpData.typeId}/${_self.topUpData.amount}`,
                            validateStatus: function (status) {
                                return status == 200 || status == 422;
                            }
                        })
                            .then(function (response) {
                                if (response.status === 422) {
                                    alert(JSON.stringify(response.data))
                                } else {
                                    response.data.error ? alert(response.data.error) : alert(response.data.message);
                                    _self.topUpData.amount = null;
                                }
                            })
                            .catch(function (err) {
                                alert(err);
                                console.log(err);
                            });
                    },
                },
            },

    }
})