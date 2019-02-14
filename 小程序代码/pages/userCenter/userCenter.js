// pages/userCenter/userCenter.js
var mask = true  // 防止多次点击
var mask2 = true  // 防止多次点击
var util = require('../../utils/util.js')
//获取应用实例
const app = getApp()
Page({
  data: {
    userInfo: {},
    hasUserInfo: false,
    token: '',
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    challenge: '-',  // 今天挑战次数
    win: '-',  // 复活卡
    total: '-',  // 总挑战次数/已挑战
    excAmount: null,  //可兑换的挑战次数
    myList: [], // 记录数据
    page: 0,
    lower: true,
    doll: 0, // 娃娃
    moreGame: [
      // {
      //   name: '围住旺财',
      //   style: '一款休闲益智类游戏',
      //   src: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   num: 13547,
      //   appid: 'wx4eb936824212af06' //小程序appid
      // }, {
      //   name: '围住旺财',
      //   style: '一款休闲益智类游戏',
      //   src: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   num: 13547,
      //   appid: 'wx4eb936824212af06' //小程序appid
      // }, {
      //   name: '围住旺财',
      //   style: '一款休闲益智类游戏',
      //   src: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   num: 13547,
      //   appid: 'wx4eb936824212af06' //小程序appid
      // }, {
      //   name: '围住旺财',
      //   style: '一款休闲益智类游戏',
      //   src: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   num: 13547,
      //   appid: 'wx4eb936824212af06' //小程序appid
      // }
    ],
    life: false, // 复活卡
    money: 1, // 金额
    jszc:"", //xxx提供技术支持
    flag2: 1,  //购买复活块开关
    flag4: 1,  //个人中心炫耀战绩开关
  },
  //支付
  payment: function () {
    wx.showLoading({
      title: '加载中...',
    })
    var that = this;
    if (mask2) {
      // console.log(util);
      var postUrl = app.setConfig.url + '/index.php/api/enve/buyRevive',
        postData = {
          token: this.data.token,
          isPlaying: 0
        };
      // app.postLogin(postUrl, postData, function (res) {
      app.request(postUrl, postData, function (res) {
        // console.log(res)
        if (res.data.code == 20000) {
          wx.hideLoading();
          var res = res.data.paymentInfo;
          wx.requestPayment({
            'timeStamp': res.timeStamp,
            'nonceStr': res.nonceStr,
            'package': res.package,
            'signType': 'MD5',
            'paySign': res.paySign,
            'success': function (res) {
              wx.showToast({
                title: '支付成功',
                icon: 'success',
                duration: 1000
              })
              var win = that.data.win *1 +1
              that.setData({
                win: win,
                life:false
              })
              app.globalData.life = win;
              // console.log(win, app.globalData.life)
            },
            'fail': function (res) {
              wx.showToast({
                title: '支付失败',
                icon: 'none',
                duration: 1000
              })
              mask = true;
            }
          })
        }
      },this)
    }
  },
  requestPayment: function (res) {
    var that = this;
    if (res.data.code == 20000) {
      
      var payInfo = res.data.data;
      wx.requestPayment({
        'timeStamp': payInfo.timeStamp,
        'nonceStr': payInfo.nonceStr,
        'package': payInfo.package,
        'signType': 'MD5',
        'paySign': payInfo.paySign,
        success: function (res) {

        },
        fail: function (res) {
          that.setData({
            life: false
          })
        }
      })
    }
  },
  //
  closeModel: function () {
    this.setData({
      life: false
    })
  },
  // 购买复活卡
  buy: function () {
    if (this.data.flag2) {
      this.setData({
        life: true
      })
    }

  },
  // 邀请码
  tosharePng:function(){
    wx.showLoading({
      title: '邀请码生成中',
      mask: true
    })
    var postUrl = app.setConfig.url + '/index.php/Api/ToCode/get_code',
      postData = {
        token: app.globalData.token,
        page: 'pages/index/index'
      };
    // app.postLogin(postUrl, postData,function(res){
      app.request(postUrl, postData, function (res) {
        // console.log(res)
        if (res.data.code === 20000) {
          wx.showToast({
            title: '生成成功',
            icon: 'success',
            duration: 1000
          })
          var fximg = app.setConfig.url + '/' + res.data.data;
          wx.previewImage({
            current: '', // 当前显示图片的http链接
            urls: [fximg] // 需要预览的图片http链接列表
          })
        }
    },this)
  },
  // 查看奖品
  toPrize: function (e) {
    if (mask) {
      mask = false;
      wx.navigateTo({
        url: '../prizeList/prizelist?mode=1',
      })
    }
  },
  // 去别的小程序
  toMore: function (e) {
    var id = e.currentTarget.dataset.id;
    var fdStart = id.indexOf("wx");
    if (mask) {
      mask = false;
      if (fdStart == 0) {
        wx.navigateToMiniProgram({
          appId: id,
          success(res) {
            // 打开成功
          }
        })
      } else if (fdStart == -1) {
        wx.makePhoneCall({
          phoneNumber: id
        })
      }
    }

  },
  // 转发
  onShareAppMessage: function (res) {
    // console.log()
    // 判断是普通转发 还是 获得挑战机会
    var info = parseInt(res.target.dataset.info);
    // var id = app.globalData.pid;
    var that = this;
    wx.showShareMenu({
      withShareTicket: true
    })
    // title = this.data.userInfo.nickName + ' ' + this.data.relay;
    if (res.from === 'button') {
      // 来自页面内转发按钮
    }
    if (!info) {
      return {
        title: this.data.userInfo.nickName + '已获得了' + this.data.doll + '个娃娃!!, 你呢?',
        path: '/pages/index/index',
        imageUrl: '/images/share.png',
        success: function (res) {
          // console.log(res.shareTickets)

        },
        fail: function (res) {
          // 转发失败
        }
      }
    } else {
      return {
        title: app.globalData.title,
        path: '/pages/index/index',
        imageUrl: '/images/share.png',
        success: function (res) {
          // console.log(res.shareTickets)

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
            } else {
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
          } else { //安卓
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
    }

  },
  shareJudge: function (shareTickets, encryptedData, iv) {
    wx.showLoading({
      title: '验证中..',
      mask: true
    })
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
            frequency
          })
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
  // 请求超时
  overdue_btn: function () {
    if (this.reloadFn) {
      // app.userLogin(this);
      this.reloadFn()
    }
    this.setData({
      overdue: false
    });
    wx.showLoading({
      title: '加载中...',
      mask:true
    })
  },
  onLoad: function () {
    app.userLogin(this);
  },
  onShow: function () {
    // 开关打开 
    mask = true;
    wx.showLoading({
      title: '加载中•••'
    });
    this.data.lower = true;
    this.data.page = 0;
    this.loop()
  },
  loop: function () {
    if (!app.globalData.token) {
      var that = this
      setTimeout(function () { that.loop(); }, 100)
    } else {
      var info = app.globalData.userInfo,
        tok = app.globalData.token,
        excAmount = app.globalData.excAmount,
        revive_money = app.globalData.revive_money,
        flag4 = app.globalData.flag4;
      if (info) {
        this.setData({
          userInfo: info,
          token: tok,
          hasUserInfo: true,
          page: 0,
          excAmount: excAmount,
          money: revive_money,
          flag4: flag4
        })
      }
      this.loaddata();
      wx.hideLoading()
    }
  },
  // 触底翻页
  lower: function () {
    this.loaddata();
  },
  //数据加载
  loaddata: function () {
    if (!this.data.lower) { return false; }
    wx.showLoading({
      title: '加载中•••'
    })
    this.data.lower = false
    var tok = this.data.token;
    var postUrl = app.setConfig.url + '/index.php/api/enve/myRecord',
      pag = this.data.page + 1,
      postData = {
        page: pag,
        token: tok
      };
    // app.postLogin(postUrl, postData, this.initial);
    app.request(postUrl, postData, this.initial,this);
  },
  initial: function (res) {
    if (res.data.code == 20000) {
      wx.hideLoading()
      let datas = res.data;
      var lower = true;
      var page = datas.data.page;
      if (datas.data.applist.length == 0){
        page = page + 1;
        lower = false;
      }
      
      if(datas.data.page == 1){
        var jszc = datas.data.jszc ? datas.data.jszc : "";
        this.setData({
          doll: datas.data.prizeAmount,
          challenge: datas.data.play_time,
          win: datas.data.revive_time,
          total: datas.data.challenge_time,
          moreGame: datas.data.applist,
          page:page,
          lower: lower,
          flag2: app.globalData.flag2,

          jszc: jszc,
        })
      }else{
        if (lower){
          var moreGame = this.data.moreGame;
          moreGame = moreGame.concat(datas.data.applist);
          this.setData({
            moreGame: moreGame,
            lower: lower,
            page:page,
            flag2: app.globalData.flag2
          })
        }
      }
/*
      var page = this.data.page + 1;
      var list = page == 1 ? [] : this.data.myList;
      list = list.concat(datas.data);
      this.setData({
        challenge: datas.num,  //挑战次数
        win: app.globalData.life,  //成功次数
        top_amount: datas.amount,  //最高分
        myList: list,    //  我的记录列表
        page: page,
        lower: true,
        thisImg: app.globalData.thisImg,
        flag: app.globalData.flag || true
      })*/
    }
  }
})