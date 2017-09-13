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
    import Options from './hourlyTableOptions'

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo
        },

        data: function () {
            return {
                vuetableUrl: Options.vuetableUrl,
                tableFields: Options.tableFields,
                sortOrder: Options.sortOrder,

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
