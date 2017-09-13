<template>
    <div>
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
</template>

<script>
    import { Vuetable, VuetablePagination, VuetablePaginationInfo}  from 'vuetable-2'

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo
        },

        data: function () {
            return {
                vuetableUrl: '/admin/api/statement/daily',

                tableFields: [
                    {
                        name: 'date',
                        title: '日期',
                    },
                    {
                        name: 'card_purchased',
                        title: '房卡购买量',
                    },
                    {
                        name: 'coin_purchased',
                        title: '金币购买量'
                    },
                    {
                        name: 'card_consumed',
                        title: '房卡消耗量'
                    },
                    {
                        name: 'coin_consumed',
                        title: '金币消耗量'
                    },
                ],

                sortOrder: [    //默认的排序
                    {
                        field: 'date',
                        sortField: 'date',
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
        },
    }
</script>
