<template>
    <div>
        <vuetable ref="vuetable"
                  :api-url="tableUrl"
                  :fields="tableFields"
                  :sort-order="tableSortOrder"
                  :css="css.table"
                  pagination-path=""
                  :per-page="15"
                  :append-params="moreParams"
                  :detail-row-component="detailRowComponent"
                  :track-by="tableTrackBy"
                  @vuetable:cell-clicked="onCellClicked"
                  @vuetable:pagination-data="onPaginationData"
                  @vuetable:checkbox-toggled="onCheckboxToggled"
                  @vuetable:checkbox-toggled-all="onCheckboxToggledAll"
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
  import Vue from 'vue'
  import {Vuetable, VuetablePagination, VuetablePaginationInfo}  from 'vuetable-2'

  export default {
    components: {
      Vuetable,
      VuetablePagination,
      VuetablePaginationInfo,
    },

    props: {
      tableUrl: {
        required: true,
      },
      tableFields: {
        type: Array,
        required: true,
      },
      tableSortOrder: {
        type: Array,
        default: function () {
          return [
            {
              field: 'id',
              sortField: 'id',
              direction: 'desc',
            },
          ]
        },
      },
      detailRowComponent: {
        type: String,
        default: null,
      },
      tableTrackBy: {
        default: 'id',
      },
      tableFilterParams: {
        default: () => ({}),
      },
      callbacks: {
        type: Object,
        default: () => ({}),
      },
    },

    data: function () {
      return {
        eventPrefix: 'MyVuetable',
        css: {
          table: {
            tableClass: 'table table-striped table-bordered',
            ascendingIcon: 'glyphicon glyphicon-chevron-up',
            descendingIcon: 'glyphicon glyphicon-chevron-down',
            handleIcon: 'glyphicon glyphicon-menu-hamburger',
            renderIcon: function (classes) {
              return `<span class="${classes.join(' ')}"></span>`
            },
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
              last: "",
            },
          },
        },
        moreParams: {},
      }
    },

    methods: {
      onPaginationData (paginationData) {
        if (paginationData.error) {     //如果后端接口返回了错误信息，则触发事件
          this.$root.eventHub.$emit(`${this.eventPrefix}:error`, paginationData)
        }
        this.$refs['pagination'].setPaginationData(paginationData)
        this.$refs.paginationInfo.setPaginationData(paginationData)
      },
      onChangePage (page) {
        this.$refs.vuetable.changePage(page)
      },
      onCellClicked (data) {
        if (this.detailRowComponent) {  //只有当传入了detailRow组件才展示detailRow
          this.$refs.vuetable.toggleDetailRow(data[this.tableTrackBy])
        } else {
          //没有注册detailRow组件时触发点击事件
          this.$root.eventHub.$emit(`${this.eventPrefix}:cellClicked`, data)
        }
      },
      onCheckboxToggled (isChecked, data) {
        this.$root.eventHub.$emit(`${this.eventPrefix}:checkboxToggled`, isChecked, data)
      },
      onCheckboxToggledAll (isChecked) {
        this.$root.eventHub.$emit(`${this.eventPrefix}:checkboxToggledAll`, isChecked, this.$refs.vuetable.selectedTo)
      },
      onFilterSet (filterText) {
        this.moreParams = {
          'filter': filterText,
        }
        Vue.nextTick(() => this.$refs.vuetable.refresh())
      },
      onTableRefresh () {
        Vue.nextTick(() => this.$refs.vuetable.refresh())
      },
      //清空checkbox选中框
      onFlushSelectedTo () {
        this.$refs.vuetable.selectedTo = []
      },
    },

    mounted: function () {
      //将传过来的回调函数绑定到组件实例上
      for (let [key, value] of _.entries(this.callbacks)) {
        this[key] = value
      }

      this.$root.eventHub.$on(`${this.eventPrefix}:refresh`, this.onTableRefresh)
      this.$root.eventHub.$on('MyFilterBar:filter', this.onFilterSet)
      this.$root.eventHub.$on(`${this.eventPrefix}:flushSelectedTo`, this.onFlushSelectedTo)
    },
  }
</script>