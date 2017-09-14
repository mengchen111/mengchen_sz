/**
 * Created by liudian on 9/14/17.
 */

import Vue from 'vue'
import axios from 'axios'

let app = new Vue({
    el: '#app',
    data: {
        loading: true,
        summaryDataApi: '/admin/api/home/summary',
        summaryData: {},
    },

    created: function () {
        let _self = this;

        axios.get(this.summaryDataApi)
            .then(function (response) {
                _self.summaryData = response.data;
                _self.loading = false;
            })
            .catch(function (err) {
                alert(err);
                console.log(err);
            })
    }
});