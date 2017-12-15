<template>
    <div class="input-group" :class="filterBarClass" :style="css">
        <input type="text" v-model.trim="filterText" class="form-control" @keyup.enter="doFilter"
               :placeholder="placeholder">
        <span class="input-group-btn">
            <button class="btn btn-flat" @click="doFilter" style="background-color: #b5bbc8">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </span>
    </div>
</template>

<script>
  export default {
    props: {
      placeholder: {
        type: String,
        default: '',
      },
      filterBarClass: {
        required: false,
        default: 'pull-right',
      },
      css: {
        required: false,
        default: 'margin-bottom: 10px;',
      },
    },
    data: function () {
      return {
        eventPrefix: 'MyFilterBar',
        filterText: '',
      }
    },

    methods: {
      doFilter () {
        this.$root.eventHub.$emit(`${this.eventPrefix}:filter`, this.filterText)
      },
    },
  }
</script>