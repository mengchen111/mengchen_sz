<template>
    <div class="row" style="margin-right: 50px;margin-bottom: 8px;">
        <div class="input-group col-xs-4 pull-right">
            <input type="text" v-model.trim="filterText" class="form-control" @keyup.enter="doFilter" :placeholder="placeholderText">
            <span class="input-group-btn">
                        <button class="btn btn-flat" @click="doFilter">
                            <i class="glyphicon glyphicon-search"></i>
                        </button>
                      </span>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            placeholderText: {
                type: String,
            }
        },
        data: function () {
            return {
                filterText: ''
            }
        },

        methods: {
            doFilter () {
                this.$root.eventHub.$emit('filterEvent', this.filterText);
            },
        }
    }
</script>