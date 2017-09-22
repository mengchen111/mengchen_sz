<template>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar" id="sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar Menu -->
            <ul class="sidebar-menu">
                <li class="header">HEADER</li>
                <!-- Optionally, you can add icons to the links -->
                <li :class="{ active: uri.home.isActive }"><a href="/admin/home"><i class="fa fa-home"></i>
                    <span>首页</span></a></li>
                <li class="treeview" :class="{ active: uri.statement.isActive }">
                    <a href="#"><i class="fa fa-pie-chart"></i> <span>数据报表</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.statement.hourly.isActive }"><a href="/admin/statement/hourly"><i
                                class="fa fa-circle-o"></i>时报表</a></li>
                        <li :class="{ active: uri.statement.daily.isActive }"><a href="/admin/statement/daily"><i
                                class="fa fa-circle-o"></i>日报表</a></li>
                        <li :class="{ active: uri.statement.monthly.isActive }"><a href="/admin/statement/monthly"><i
                                class="fa fa-circle-o"></i>月报表</a></li>
                    </ul>
                </li>
                <li class="treeview" :class="{ active: uri.player.isActive }">
                    <a href="#"><i class="fa fa-gamepad"></i> <span>玩家管理</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.player.list.isActive }"><a href="/admin/player/list"><i
                                class="fa fa-circle-o"></i>玩家列表</a></li>
                    </ul>
                </li>
                <li class="treeview" :class="{ active: uri.gm.isActive }">
                    <a href="#"><i class="fa fa-wrench"></i> <span>GM功能</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.gm.notification.isActive }"><a href="#"><i class="fa fa-bell"></i>公告管理
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                            <ul class="treeview-menu">
                                <li :class="{ active: uri.gm.notification.marquee.isActive }"><a
                                        href="/admin/gm/notification/marquee"><i class="fa fa-circle-o"></i>跑马灯公告</a>
                                </li>
                                <!--<li :class="{ active: uri.gm.notification.login.isActive }"><a href="/admin/gm/notification/login"><i class="fa fa-circle-o"></i>登录公告</a></li>-->
                            </ul>
                        </li>
                        <li :class="{ active: uri.gm.room.isActive }"><a href="#"><i class="fa fa-home"></i>房间管理
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                            <ul class="treeview-menu">
                                <li :class="{ active: uri.gm.room.friend.isActive }"><a href="/admin/gm/room/friend"><i
                                        class="fa fa-circle-o"></i>解散好友房</a></li>
                                <li :class="{ active: uri.gm.room.coin.isActive }"><a href="/admin/gm/room/coin"><i
                                        class="fa fa-circle-o"></i>解散金币房</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="treeview" :class="{ active: uri.stock.isActive }">
                    <a href="#"><i class="fa fa-suitcase"></i> <span>库存管理</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.stock.apply_request.isActive }"><a
                                href="/admin/stock/apply-request"><i class="fa fa-circle-o"></i>申请库存</a></li>
                        <li :class="{ active: uri.stock.apply_list.isActive }"><a href="/admin/stock/apply-list"><i
                                class="fa fa-circle-o"></i>申请列表</a></li>
                        <li :class="{ active: uri.stock.apply_history.isActive }"><a
                                href="/admin/stock/apply-history"><i class="fa fa-circle-o"></i>审核记录</a></li>
                    </ul>
                </li>
                <li class="treeview" :class="{ active: uri.agent.isActive }">
                    <a href="#"><i class="fa fa-users"></i> <span>代理商管理</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.agent.list.isActive }"><a href="/admin/agent/list"><i
                                class="fa fa-circle-o"></i>代理商列表</a></li>
                        <li :class="{ active: uri.agent.create.isActive }"><a href="/admin/agent/create"><i
                                class="fa fa-circle-o"></i>新建代理商</a></li>
                    </ul>
                </li>
                <li class="treeview" :class="{ active: uri.top_up.isActive }">
                    <a href="#"><i class="fa fa-credit-card"></i> <span>充值记录</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.top_up.admin.isActive }"><a href="/admin/top-up/admin"><i
                                class="fa fa-circle-o"></i>管理员充值记录</a></li>
                        <li :class="{ active: uri.top_up.agent.isActive }"><a href="/admin/top-up/agent"><i
                                class="fa fa-circle-o"></i>代理商充值记录</a></li>
                        <li :class="{ active: uri.top_up.player.isActive }"><a href="/admin/top-up/player"><i
                                class="fa fa-circle-o"></i>玩家充值记录</a></li>
                    </ul>
                </li>
                <li class="treeview" :class="{ active: uri.system.isActive }">
                    <a href="#"><i class="fa fa-gear"></i> <span>系统管理</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li :class="{ active: uri.system.log.isActive }"><a href="/admin/system/log"><i
                                class="fa fa-circle-o"></i>系统日志</a></li>
                    </ul>
                </li>
            </ul>
            <!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
    </aside>
</template>

<script>
  export default {
    data: function () {
      return {
        uri: {
          home: {
            isActive: false
          },
          statement: {
            isActive: false,
            hourly: {
              isActive: false
            },
            daily: {
              isActive: false
            },
            monthly: {
              isActive: false
            }
          },
          player: {
            isActive: false,
            list: {
              isActive: false
            },
            top_up: {
              isActive: false
            }
          },
          gm: {
            isActive: false,
            notification: {
              isActive: false,
              marquee: {
                isActive: false
              },
              login: {
                isActive: false
              }
            },
            room: {
              isActive: false,
              friend: {
                isActive: false
              },
              coin: {
                isActive: false
              }
            }
          },
          stock: {
            isActive: false,
            apply_request: {
              isActive: false
            },
            apply_list: {
              isActive: false
            },
            apply_history: {
              isActive: false
            },
          },
          agent: {
            isActive: false,
            create: {
              isActive: false
            },
            list: {
              isActive: false
            },
          },
          top_up: {
            isActive: false,
            admin: {
              isActive: false
            },
            agent: {
              isActive: false
            },
            player: {
              isActive: false
            },
          },
          system: {
            isActive: false,
            log: {
              isActive: false
            },
          }
        }
      }
    },

    methods: {},

    created: function () {
      let _self = this
      let accessedUri = location.href.match(/http:\/\/[\w.-]+\/admin\/([\w/-]+)/)[1]
        .replace('-', '_')
        .split('/')
      accessedUri.reduce(function (lastValue, currentValue) {
        lastValue[currentValue].isActive = true
        return lastValue[currentValue]
      }, _self.uri)
    }
  }
</script>