<template>
    <!-- Main Header -->
    <header class="main-header">

        <!-- Logo -->
        <a href="#" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>梦</b>晨</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>梦晨</b>管理后台</span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <!-- Left Menu -->
            <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class=""><a href="#">房卡库存：{{ inventoryAmount.cards }}</a></li>
                    <li class=""><a href="#">金币库存：{{ inventoryAmount.coins }}</a></li>
                </ul>
            </div>

            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account Menu -->
                    <li class="dropdown user user-menu">
                        <!-- Menu Toggle Button -->
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="hidden-xs">{{ adminInfo.account }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-footer">
                                <div class="pull-left">
                                    <button class="btn btn-default btn-flat" data-toggle="modal"
                                            data-target="#change-password-modal">
                                        修改密码
                                    </button>
                                </div>
                                <div class="pull-right">
                                    <form id="logout-form" action="/logout" method="POST"
                                          @submit.prevent="logoutAction">
                                        <button type="submit" class="btn btn-default btn-flat">退出登录</button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
</template>

<script>
  import axios from 'axios'

  export default {
    data: function () {
      return {
        adminInfo: {            //当前登录的管理员信息
          account: 'admin',
        },
        infoApi: '/api/info',
        inventoryAmount: {
          cards: 0,
          coins: 0,
        }
      }
    },
    methods: {
      logoutAction () {
        axios.post('/logout')
          .then(function (response) {
            console.log(response)
            window.location.href = '/'
          })
      }
    },

    created: function () {
      let _self = this

      axios.get(this.infoApi)
        .then(function (response) {
          _self.adminInfo = response.data

          if (_self.adminInfo.inventorys.length > 0) {
            for (let inventory of _self.adminInfo.inventorys) {
              switch (inventory.item.name) {
                case '房卡':
                  _self.inventoryAmount.cards = inventory.stock
                  break
                case '金币':
                  _self.inventoryAmount.coins = inventory.stock
                  break
                default:
                  break
              }
            }
          }
        })
    },
  }
</script>