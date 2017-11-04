import '../common.js'
import MyToastr from '../../../components/MyToastr.vue'
import {Checkbox, Radio} from 'vue-checkbox-radio'

new Vue({
  el: '#app',
  components: {
    Checkbox,
    Radio,
    MyToastr,
  },
  data: {

  },

  methods: {
    displayOpenRoom () {
      console.log('display open room')
    },

    displayRoomHistory () {
      console.log('display room history')
    },

    createRoom () {
      console.log('create room')
    }
  },
})