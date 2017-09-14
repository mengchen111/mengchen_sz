/**
 * Created by liudian on 9/14/17.
 */

import { Vuetable, VuetablePagination, VuetablePaginationInfo}  from 'vuetable-2'
import FilterBar from '../../../components/FilterBar.vue'
import axios from 'axios'

let tableTemplate = `
    <div>
        <filter-bar :placeholderText="filterPlaceholderText"></filter-bar>
        <vuetable ref="vuetable"
                  :api-url="vuetableUrl"
                  :fields="tableFields"
                  :sort-order="sortOrder"
                  :css="css.table"
                  pagination-path=""
                  :per-page="15"
                  :append-params="moreParams"
                  @vuetable:pagination-data="onPaginationData"
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

Vue.component('custom-actions', {
    template: `
        <div class="row">
            <button class="btn btn-small btn-danger btn-flat" @click="dismissRoom(rowData)">
                解散房间
            </button>
            <div class="overlay" v-show="loading"><i class="fa fa-refresh fa-spin"></i></div>
        </div>`,
    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        }
    },
    data: function () {
        return {
            loading: false,
        }
    },
    methods: {
        dismissRoom (rowData) {
            let _self = this;
            this.loading = true;
            axios.delete(`/admin/api/game/room/friend/${rowData.owner}`)
                .then(function (response) {
                    _self.loading = false;
                    return response.data.error ? alert(response.data.error) : alert(response.data.message);
            })
        }
    }
});

let app = new Vue({
    el: '#app',
    data: {
        eventHub: new Vue(),     //中心事件处理器
    },
    components: {
        'my-vuetable': {
            components: {
                Vuetable,
                VuetablePagination,
                VuetablePaginationInfo,
                FilterBar,
            },

            template: tableTemplate,
            data: function () {
                return {
                    filterPlaceholderText: '查找房间ID',    //filterbar组件的placeholder文字
                    vuetableUrl: '/admin/api/game/room/friend',

                    tableFields: [
                        {
                            name: 'owner',
                            title: '房主ID',
                        },
                        {
                            name: 'id',
                            title: '房间ID',
                        },
                        {
                            name: 'open_id',
                            title: '用户账号'
                        },
                        {
                            name: 'create_time',
                            title: '创建时间'
                        },
                        {
                            name: '__component:custom-actions',
                            title: '操作',
                            titleClass: 'text-center',
                            dataClass: 'text-center',
                        },
                    ],

                    sortOrder: [    //默认的排序
                        {
                            field: 'owner',
                            sortField: 'owner',
                            direction: 'desc',
                        }
                    ],

                    css: {
                        table: {
                            tableClass: 'table table-striped table-bordered',
                            ascendingIcon: 'glyphicon glyphicon-chevron-up',
                            descendingIcon: 'glyphicon glyphicon-chevron-down',
                            handleIcon: 'glyphicon glyphicon-menu-hamburger',
                            renderIcon: function(classes, options) {
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
                }
            },

            mounted: function () {
                let _self = this;

                this.$root.eventHub.$on('filterEvent', function (filterText) {
                    _self.moreParams = {
                        'filter': filterText,
                    };
                    Vue.nextTick(function (){
                        _self.$refs.vuetable.refresh();
                    })
                });
            },

            methods: {
                onPaginationData (paginationData) {
                    this.$refs.pagination.setPaginationData(paginationData);
                    this.$refs.paginationInfo.setPaginationData(paginationData);
                },
                onChangePage (page) {
                    this.$refs.vuetable.changePage(page);
                },
            },
        }
    }
});