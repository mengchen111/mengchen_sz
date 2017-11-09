<template>
    <div>
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
  import { VuetablePagination, VuetablePaginationInfo }  from 'vuetable-2'

  export default {
    components: {
      VuetablePagination,
      VuetablePaginationInfo,
    },

    props: {
      paginationUrl: {
        required: true,
      },
      perPage: {
        type: Number,
        default: 15,
      },
    },

    data: function () {
      return {
        eventPrefix: 'MyPagination',
        currentPage: 1,
        css: {
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
      }
    },

    watch: {
      paginationUrl: function (newVal, oldVal) {
        if (newVal !== oldVal) {
          this.currentPage = 1
          this.onChangePage(this.currentPage)
        }
      },
    },

    methods: {
      setPagination (paginationData) {        //设置分页消息和分页数据
        this.$refs['pagination'].setPaginationData(paginationData)
        this.$refs.paginationInfo.setPaginationData(paginationData)
      },
      onChangePage (page) {                     //点击分页按钮会触发此事件方法，获取指定页面的数据
        this.setPage(page)
        let _self = this

        axios.get(this.paginationUrl + `?page=${this.currentPage}&per_page=${this.perPage}`)
          .then(function (res) {
            if (res.data.error) {     //如果后端接口返回了错误信息，则触发错误消息的事件
              return _self.$root.eventHub.$emit(`${_self.eventPrefix}:error`, res.data)
            }
            _self.setPagination(res.data)   //更新分页信息
            _self.$root.eventHub.$emit(`${_self.eventPrefix}:data`, res.data.data)    //将当前页面的data数组通过事件返回，其他分页信息不输出
          })
          .catch(function (err) {
            _self.$root.eventHub.$emit(`${_self.eventPrefix}:error`, err)
          })
      },
      setPage (page) {
        switch (page) {
          case 'next':
            this.currentPage += 1
            break
          case 'prev':
            this.currentPage -= 1
            break
          default:
            this.currentPage = page
        }
      },
    },

    created: function () {
      if (this.paginationUrl) {
        this.onChangePage(this.currentPage)  //组件生成时获取第一页的数据
      }
    },

    mounted: function () {
      this.$root.eventHub.$on(`${this.eventPrefix}:changePage`, this.onChangePage)
    },
  }
</script>