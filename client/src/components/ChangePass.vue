<template>
    <!-- 重置密码提示框 -->
    <div id="change-password-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog" style="width: 380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal">x</button>
                    <h3 class="text-center">修改密码</h3>
                </div>
                <div class="modal-body">
                    <form role="form" class="form-group" method="POST" action="#"
                          @submit.prevent="changePasswordAction">
                        <div class="form-group has-feedback">
                            <input name="password" type="password" class="form-control" required
                                   placeholder="旧密码" v-model.trim="formData.password">
                            <span class="fa fa-user-secret form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <input name="new_password" type="password" class="form-control" required
                                   placeholder="新密码" v-model.trim="formData.new_password">
                            <span class="fa fa-pencil form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <input name="new_password_confirmation" type="password" class="form-control" required
                                   placeholder="再次输入新密码" v-model.trim="formData.new_password_confirmation">
                            <span class="fa fa-pencil form-control-feedback"></span>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-primary btn-block btn-flat" type="submit">提交</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import axios from 'axios'

  export default {
    data: function () {
      return {
        formData: {
          password: '',
          new_password: '',
          new_password_confirmation: '',
        },
      }
    },

    methods: {
      changePasswordAction () {
        let _self = this
        let role = location.href.match(/http:\/\/[\w.-]+\/([\w-]+\/)/)[1]    //管理员还是代理商

        axios({
          method: 'PUT',
          url: `/${role}api/self/password`,
          data: _self.formData,
          validateStatus: function (status) {     //定义哪些http状态返回码会被promise resolve
            return status == 200 || status == 422
          },
        })
          .then(function (response) {
            if (response.status === 422) {
              return alert(JSON.stringify(response.data))
            }
            response.data.error ? alert(response.data.error) : alert(response.data.message)
            for (let index of _.keys(_self.formData)) {
              _self.formData[index] = ''
            }
            return true
          })
          .catch(function (err) {
            alert(err)
          })
      },
    }
  }
</script>