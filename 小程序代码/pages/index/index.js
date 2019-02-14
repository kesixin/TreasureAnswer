//index.js
// var overdue = require('../../overdue/overdue.js');
//获取应用实例
const app = getApp()
var mask = true
var lower = true
var loadct = true
var kzq = true    //走马灯控制器
var timer = null;
var num =0;
var dsqload = null;   //加载页面的定时器
const innerAudioContext = wx.createInnerAudioContext();
innerAudioContext.autoplay = true;
Page({
  data: {
    auth: false,
    userInfo: {},   // 用户信息
    hasUserInfo: false,  // 用户授权
    canIUse: wx.canIUse('button.open-type.getUserInfo'),   //  检测小程序版本兼容
    // res: null, // 挑战页面数据
    rule: false,
    ruleText: [
      '每人每天有3次答题机会，分享到不同微信群可获得额外3次答题机会。',
      '每次挑战包含12道题目，全部答对即可获得随机娃娃奖品。',
      '答错题目可以转发到不同微信群换取立即复活并继续答题的机会，每次答题最多有3次复活机会，大于3次可以购买复活卡继续答题，每次挑战最多可购买3次。',
      '领取活动奖品需关注我们的微信公众号并联系在线客服。',
      '如有任何问题请联系我们客服。'
    ], // 规则
    gametext: [
      '一口气答对12题即可领取娃娃奖品',
      '答错可转发到群或购买复活卡获取继续答题机会'
    ],
    money: '', // 福利
    info: '', // 信息
    more: 0, // 是否显示更多游戏
    share_text: "",
    share_bg:"",
    frequency: 0 ,//次数
    show_share:false,
    // 夺宝
    // 总次数
    total: 23414,
    // 标题
    right: '答对12题随机送娃娃',
    // 礼物
    prize: [
      {
        src: '/images/db/index/prize1.png',
        text: '泰迪熊'
      }, {
        src: '/images/db/index/prize2.jpg',
        text: '邦尼兔'
      },
    ],
    flag2: 0,
    page: 1,
    percent:0,   // 加载进度值
    show: true,
    loadct: false,
    sildeTxt:[    //获得新消息数组
      // {
      //   head_img: "https://wx.qlogo.cn/mmopen/vi_32/hzcI0l9A7zFaX3mdZDKfY1x71icvNMaHxqJdJ5lZZd1A4nH9gBG8NJQZpL8CzvGib4NUmiaibMzSlneiapJ8us4XNxA/0",
      //   nick_name: "啦啦啦",
      //   prize_name:"泰迪熊"
      // },
      // {
      //   head_img: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIImyIibGHianiaEOyYLbFKMKkVsBPj9S5QncqWcoAOIJI1pcMwzwd8YmgV3dgcPg97IaXLk3hxGFiaSw/0",
      //   nick_name: "去去去",
      //   prize_name:"泰迪熊"
      // },
      // {
      //   head_img: "https://wx.qlogo.cn/mmopen/vi_32/bGXfAK9NreIjCGLgjiaibOXLCpR8ZMIAjpxpI035iasj41DuQOgUqxVFGVPdtuibkjrzlL3S3xADM05MUlX3UhxDaw/0",
      //   nick_name: "啛啛喳喳",
      //   prize_name:"泰迪熊"
      // },
    ],
    // 选中显示的新消息
    news:{

    },
    // 消息变更控制器
    newchange:false,
  },
  //事件处理函数
  // 分页
  lower: function (e) {
    // console.log('lower');
    e.cancelBubble = true;
    // e.stopPropagation();
    if (lower) {
      lower = false;

      var that = this;
      var page = this.data.page;
      page++;
      var postUrl = app.setConfig.url + '/index.php?g=Api&m=Enve&a=getGlobalInfo';
      var postData = {
        token: app.globalData.token,
        page: page
      };
      // app.postLogin(postUrl, postData, function(res){
      app.request(postUrl, postData, function (res){
        var data = res.data.data;
        var prizes = data.prize;
        var prize = that.data.prize;
        if(prizes.length == 0){
          wx.showToast({
            title: '没有更多了',
            icon: 'none',
            mask: true,
            duration: 800
          })
          return false;
        }else {
          prize.push(...prizes);
          that.setData({
            prize,
            page
          })
        }
        lower = true
      }, this);
    }
  },
  // 滚轮
  scrollTop: function () {
    wx.pageScrollTo({
      scrollTop: 500,
      duration: 300
    })
  },

  //弹出游戏规则
  toRule: function () {
    this.setData({
      rule: true
    })
  },
  //关闭
  closeRule: function () {
    this.setData({
      rule: false
    })
  },
  //查看攻略 客服
  toStrategy: function () {

  },
  // 游戏开始
  toStart: function () {
    innerAudioContext.stop();
    innerAudioContext.src = '/music/star_btn.mp3'
    innerAudioContext.play();
    if ( mask ) {
      mask = false
      var frequency = this.data.frequency
      // 挑战次数判断
      // console.log(frequency)
      if (frequency == 0){
        if (this.data.flag == 1){
          this.setData({
            show_share:true,
          })
          mask = true
          return false;
        }
        else{
          wx.showToast({
            title: '没有挑战次数',
            icon: 'loading',
            mask: true,
            duration: 1500
          })
          mask = true
          return false;
        }
      }
      // var rule = {
      //   ruleText: this.data.gametext
      // }
      // var value = JSON.stringify(rule);
      wx.redirectTo({
        url: '../game/game',
      })
    }
  },
  //
  // 转发
  onShareAppMessage: function (res) {
    // var id = app.globalData.pid;
    var that = this;
    wx.showShareMenu({
      withShareTicket: true
    })
      // title = this.data.userInfo.nickName + ' ' + this.data.relay;
    if (res.from === 'button') {
      // 来自页面内转发按钮
      // console.log(res.target)
    }
    return {
      title: app.globalData.title,
      path: '/pages/index/index',
      imageUrl: '/images/share.png',
      success: function (res) {
        // console.log(res.shareTickets)

        // var shareTickets = res.shareTickets[0];
        var shareTickets;
        if (res.shareTickets) {
          shareTickets = res.shareTickets[0] || '';
        }
        var platform;  //机型
        wx.getSystemInfo({
          success: function (res) {
            platform = res.platform;
          }
        })
        // 判断是不是群聊
        if (platform == 'ios') { //苹果
          if (!res.shareTickets) { //个人
            wx.showToast({
              title: '只能是群聊哦',
              icon: 'loading',
              duration: 1500,
              mask: true
            })
          }else {
            wx.getShareInfo({
              shareTicket: shareTickets,
              success: function (res) { //是群聊
                // console.log(res.encryptedData)
                that.shareJudge(shareTickets, res.encryptedData, res.iv);
              },
              fail: function () {

              }
            })
          }
        }else { //安卓
          wx.getShareInfo({
            shareTicket: shareTickets,
            success: function (res) { //是群聊
              // console.log(res.encryptedData)
              that.shareJudge(shareTickets, res.encryptedData, res.iv);
            },
            fail: function () {
              wx.showToast({
                title: '只能是群聊哦',
                icon: 'loading',
                duration: 1500,
                mask: true
              })
            }
          })
        }
      },
      fail: function (res) {
        // 转发失败
      }
    }
  },

  shareJudge: function (shareTickets, encryptedData, iv) {
    var that = this;
    var frequency = this.data.frequency;
    var postUrl = app.setConfig.url + '/index.php/api/enve/shareGetTimes',
      postData = {
        token: app.globalData.token,
        isPlaying: 0,
        shareTicket: shareTickets,
        encryptedData: encryptedData,
        iv: iv,
        isgroup: 0
      };
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res);
      if (res.data.code == 20000) {
        wx.showToast({
          title: '转发成功',
          icon: 'success',
          duration: 1500,
          mask: true
        })
        frequency++;
        setTimeout(function () {
          that.setData({
            frequency,
            show_share: false
          })
          // console.log(frequency)
        }, 1500)
      } else {
        wx.showToast({
          title: '要不同的群哦',
          icon: 'loading',
          duration: 1500,
          mask: true
        })
      }
    },this)
  },

  clearpop: function () {
    clearTimeout(dsqload)
  },

  // 请求超时
  overdue_btn: function () {
    this.toload(0);
    if (this.reloadFn && !app.globalData.token) {
      app.userLogin(this);
      // this.reloadFn()
    }else{
      this.reloadFn();
    }
    this.setData({
      overdue: false,
      percent: 0
    });
  },

  // 第一次加载页面才执行
  onLoad: function () {
    var that = this;
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          that.setData({
            auth:true
          });
        }
      }
    });
    app.userLogin(this);
  },

  reloadInit: function() {
    wx.reLaunch({
      url: '/pages/index/index'
    })
  },

  onShow: function () {
    // if (this.data.hasUserInfo){
    //   return false
    // }
    
    mask = true;
    if (loadct) {
    // console.log(1111222)
      this.setData({
        loadct: true
      })
      var percent = this.data.percent
      this.toload(percent);
    }
    // if (overdue) {
    //   this.setData({
    //     overdue: true
    //   })
    // }
    if (app.globalData.token) {
      this.ifHasUserInfo();
      wx.hideLoading()
    }
    else {
      var that = this
      app.tokenReadyCallback = function () {
        that.ifHasUserInfo();
        wx.hideLoading()
      }
    }

  },

  ifHasUserInfo: function () {
    var info = app.globalData.userInfo,
      tok = app.globalData.token,
      postData = {},
      // pid = app.globalData.pid || pid,
      postUrl = '';
    // console.log(info)
    this.setData({
      token: tok,
      userInfo: info,
      hasUserInfo: true
    })
    postUrl = app.setConfig.url + '/index.php?g=Api&m=Enve&a=getGlobalInfo';
    postData = {
      token: tok,
      page: 1
    };
    // app.postLogin(postUrl, postData, this.initial);
    app.request(postUrl, postData, this.initial ,this)
  },

  initial: function (res) {
    if (res.data.code == 20000) {
      var data = res.data.data;
      // console.log(data)
      var ruleText = data.rule_text;
      var money = data.money;
      var more = parseInt(data.moreFlag);
      // 无挑战机会弹框按钮开关
      app.globalData.flag = data.flag || 0;
      // 复活开关
      app.globalData.flag2 = data.flag2 || 0;
      //群内智力开关
      app.globalData.flag3 = data.flag3 || 1;
      //个人中心炫耀战绩开关
      app.globalData.flag4 = data.flag4 || 1;
      //挑战完成"获得更多挑战机会"开关
      app.globalData.flag5 = data.flag5 || 1;
      //"分享到群立即复活"按钮开关
      app.globalData.flag6 = data.flag6 || 1;
      // app.globalData.flag = 1;
      // app.globalData.rule_text = data.ruleText;
      app.globalData.frequency = data.ctime || 0;
      app.globalData.title = !data.title ? '究竟谁是答题王, 等你来挑战! !' : data.title;
      // 游戏中可以转发多少次
      app.globalData.nowfrequency = data.share_revive_time || 3;
      // 复活可以几次
      app.globalData.buy_revive_time = data.buy_revive_time || 3;
      
      // 一天最大挑战次数
      app.globalData.max_challenge_time = data.max_challenge_time || 10;
      // 最大领奖品
      app.globalData.max_get_prize_time = data.max_get_prize_time || 3;
      // 复活卡金额
      app.globalData.revive_money = data.revive_money;
      // 复活卡数量
      app.globalData.life = parseInt(data.life);
      // 转发的标题
      app.globalData.title = data.title;
      // 说明
      app.globalData.ruleText = data.game_text;
      // 可兑换的挑战次数
      app.globalData.excAmount = data.excAmount ? data.excAmount : 3;
      //分享块的文字
      var share_text = data.guide_txt ? data.guide_txt : "邀请好友，帮忙答题";
      // 分享块的背景图
      var share_bg = data.share_bg ? data.share_bg : "/images/db/index/share.png"
      // console.log(data.excAmount, app.globalData.excAmount)
      wx.hideLoading()
      this.setData({
        ruleText,
        money,
        more,
        // share_text,
        frequency: app.globalData.frequency,
        flag: app.globalData.flag,
        prize: data.prize,
        right: data.right,
        total: data.total,
        sildeTxt: data.sildeTxt,
        newchange: false,
        share_text: share_text,
        share_bg: share_bg,
      })
      // console.log(data.sildeTxt)
      var that = this;
      if (timer) { clearTimeout(timer);}
      timer = setTimeout(function(){
        that.changenews(0);
      },2000)


    }
    var that = this;
    this.setData({
      percent: 100
    })
    // console.log(this.data.hasUserInfo)
    loadct = false;   //全局的
    setTimeout(function () {
      that.setData({
        show: false,
        loadct: false
      })
    }, 600)
  },
  colseShare: function () {
    this.setData({
      show_share: false
    })
  },

  // 屏幕关闭事件
  onHide: function(){
    clearTimeout(dsqload);
  },
  toload: function (num) {
    var that = this;
    var time_interval;
    var hasinfo = this.data.hasUserInfo;
    if (this.data.percent > 98 || hasinfo) {
      return false;
    }
    if (num < 80 && !hasinfo) {
      time_interval = 60;

    }
    else if (80 <= num < 97 && !hasinfo) {
      time_interval = 100;

    }
    else if (97 <= num < 100 && !hasinfo) {
      time_interval = 1000;

    }

    dsqload = setTimeout(function (){
      num++;
      that.setData({
        percent: num
      })
      that.toload(num)
    }, time_interval )
    // console.log(time_interval)
  },
  
  changenews: function (num) {
    var that = this;
    var sildeTxt = that.data.sildeTxt;
    var news = sildeTxt[num];
    timer = setTimeout(function(){
      // console.log(num)
      that.setData({
        news: news,
        newchange:true,
      })
      timer = setTimeout(function () {
        that.setData({
          newchange:false
        })
        if (num >= sildeTxt.length-1){
          that.changenews(0)
          return false
        }else{
          num++;
          that.changenews(num)
        }
      }, 6100)
    },300)
  },
})
