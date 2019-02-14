// pages/complete/complete.js
const app = getApp();
var mask = true;
var overdue_btn = true;   //判断是否是第一次点击提交按钮
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},
    text: '看来你也是芸芸众生的一员, 水平很一般嘛!', // 获奖词
    success: true, //是否成功
    ranking: '180', // 排名
    sum: 480,
    money: 1.22,
    relay: '邀请你挑战王者问答, 快来吧>>>',// 转发后显示的提示语
    state: false, // 两 版本  正常/不
    stateNum: 0, // 是否领过
    stateText: '手慢了哦，赏金都被领光啦!',
    stateText2: '本期10000个现金奖励已放完，请明日12:00再来挑战吧！',
    open: true, // 红包
    show: false, // 红包框
    // 问答者回答的对错
    qaAnswer: [1,1,0,1,0,0],
    // 对的题数
    qaYes: 4,
    // 挑战者次数
    frequency: 0,
    frequencyModel: false,
    // 
    prize: {
      // src: '/images/db/index/prize1.png',
      // text: '泰迪熊'
    },
    status: true,
    toprize:{},  //填写物流信息时的奖品信息
    okexchange:false,    //提交物就信息成功弹框
    flag5: 1,  //挑战完成"获得更多挑战机会"开关
  },
  // 事件
  // 地址
  submit: function (e) {
    if (overdue_btn){
      console.log(1111)
      var phone = e.detail.value.phone;
      var re = /^(13[0-9]|14[0-9]|15[0-9]|18[0-9])\d{8}$/;
      var retu = phone.match(re);
      var that = this;
      if (!e.detail.value.name) {
        wx.showLoading({
          title: '姓名不能为空',
          mask: true,
          duration: 1500,
        })
        return false
      }
      if (!phone) {
        wx.showLoading({
          title: '电话不能为空',
          mask: true,
          duration: 1500,
        })
        return false
      }
      if (!e.detail.value.address) {
        wx.showLoading({
          title: '地址不能为空',
          mask: true,
          duration: 1500,
        })
        return false
      }
      if (!retu) {
        wx.showLoading({
          title: '电话格式有误',
          mask: true,
          duration: 1500,
        })
        return false
      }
      else {
        mask = false
        wx.showLoading({
          title: '提交中',
          mask: true
        })
        // return false
      }
      var postUrl = app.setConfig.url + "/index.php/api/enve/saveExpressInfo",
        postData = {
          challengeId: this.data.challengeId,
          name: e.detail.value.name,
          phone: e.detail.value.phone,
          address: e.detail.value.address,
          token: app.globalData.token
        }
      app.request(postUrl, postData, function (res) {
        // app.postLogin(postUrl, postData, function(res){
        mask = true;
        if (res.data.code != 20000) {
          wx.showLoading({
            title: '兑换失败',
            mask: true
          })
          setTimeout(function () {
            wx.hideLoading();
          }, 1500)
        } else {
          wx.hideLoading();
          that.setData({
            okexchange: true
          })
          // wx.showLoading({
          //   title: '已成功兑换娃娃',
          //   mask: true
          // })
          setTimeout(function () {
            wx.hideLoading();
            that.setData({
              status: false
            })
          }, 1500)
        }
      }, this)
    }  
  },

  back: function () {
    this.setData({
      okexchange: false,
      logistics: false,
    })
    // wx.redirectTo({
    //   url: '../prizeList/prizelist?mode=1'
    // })
  },
  // 兑换娃娃 收集信息
  tologistics: function () {
    if(mask) {
      mask = false;
      this.setData({
        logistics: true
      })
    }
  },
  // 提示 
  closeFrequencyModel: function () {
    this.setData({
      frequencyModel: false
    })
  },
  // 返回首页
  tolose: function () {
    wx.reLaunch({
      url: '/pages/index/index',
    })
  },
  // 再次挑战
  toMore: function () {
    // 判断是否还可以挑战
    if (this.data.frequency > 0) {
      wx.switchTab({
        url: '/pages/index/index',
      })  
    }else {
      this.setData({
        frequencyModel: true
      })
    }
    
  },
  // 生成证书
  toShow: function () {
    // wx.navigateTo({
    //   url: '../certificate/certificate',
    // })
    var postUrl = app.setConfig.url + '/index.php/api/toCode/get_code',
      postData = {
        token: app.globalData.token,
        // pid: app.globalData.pid,
        page: 'pages/index/index'
      }
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res.data.data)
      var url = app.setConfig.url + '/' + res.data.data;
      // console.log(url)
      wx.previewImage({
        urls: [url]
      })
    },this)
  },
  // 打开红包
  toOpen: function () {
    this.setData({
      open: false
    })
  },
  // 关闭红包框
  toClose: function () {
    this.setData({
      show: false
    })
  },
  // 提现
  toWithdrawals: function () {
    wx.navigateTo({
      url: '',
    })
  },
  // toshare: function () {
  //   var id = app.globalData.pid,
  //     title = this.data.userInfo.nickName + ' ' + this.data.relay;
  //   if (res.from === 'button') {
  //     // 来自页面内转发按钮
  //     console.log(res.target)
  //   }
  //   return {
  //     title: title,
  //     path: '/pages/index/index?pid=' + id,
  //     success: function (res) {
  //       // 转发成功
  //       wx.showToast({
  //         title: '转发成功',
  //         icon: 'success',
  //         duration: 2000
  //       })
  //     },
  //     fail: function (res) {
  //       // 转发失败
  //     }
  //   }
  // },
  // 转发
  onShareAppMessage: function (res) {
    // var id = app.globalData.pid,
    // console.log(this.data.userInfo.nickName, this.data.relay)
      // title = this.data.userInfo.nickName + ' ' + this.data.relay;
    var that = this;
    wx.showShareMenu({
      withShareTicket: true
    })
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
    overdue_btn = false
    this.setData({
      overdue: false
    });
    wx.showLoading({
      title: '提交中...',
      mask: true
    })
  },
  onLoad: function (options) {

    app.userLogin(this);

    wx.showLoading({
      title: '加载中•••',
      mask: true
    });
    // 对错情况
    var qaAnswer = app.globalData.qaAnswer,
      qaYes = app.globalData.qaYes,
      success = app.globalData.success;
    var toprize={};
    toprize.prize_img = options.src;
    toprize.prize_name = options.doll_name;
    this.setData({
      qaAnswer,
      qaYes,
      success,
      prize: app.globalData.endPrize,
      challengeId: options.challengeId,
      toprize: toprize,
      // frequency: app.globalData.ctime,
      // thisImg: app.globalData.thisImg,

    })
    // var scene = decodeURIComponent(options.scene);
    // if (scene > 0) {
    //   var pid = scene;
    // } else {
    //   var pid = options.pid;
    // }
    /* 
    
    var pid = app.globalData.pid;
    var obj = app.globalData.complete;
    var info = app.globalData.info;
    var more = app.globalData.more;
    console.log(obj)
    var money = '';
    // 成功
    if (obj.is_pass) {
      if (obj.getInfo.code == 1) {
        var show = true
      }
      if (obj.getInfo.code == 1) {//版本1
        var state = true;
        money = obj.getInfo.money;
      } else if (obj.getInfo.code == 2 || obj.getInfo.code == 3) { // 版本2
        var state = false;
      }
      // 曾经领过
      if (obj.getInfo.code == 2) {
        var stateNum = obj.getInfo.money;
      }
      // 失效
      if (obj.getInfo.code == 3) {
        var stateText = obj.getInfo.msg;
      }
    } else { //失败
      var show = false;
      var state = true;
      money = '0.00';
    }
    this.setData({
      success: obj.is_pass,
      ranking: obj.rank,
      money: money || '0.00',
      sum: obj.total,
      text: obj.text || '看来你也是芸芸众生的一员, 水平很一般嘛!',
      state: state,
      stateText: stateText || this.data.stateText,
      show,
      stateNum,
      info,
      more
    })

    */
    
    this.loop();
  },
  loop: function () {
    if (!app.globalData.token) {
      var that = this
      setTimeout(function () { that.loop(); }, 100)
    } else {
      var info = app.globalData.userInfo,
        tok = app.globalData.token,
        flag5 = app.globalData.flag5;
      if (!info) {
        app.userInfoReadyCallback = res => {
          this.setData({
            userInfo: res.userInfo,
            token: tok,
            flag5: flag5
          })
        }
      } else {
        // console.log(info);
        this.setData({
          userInfo: info,
          token: tok,
          flag5: flag5
        })
      }
      this.initial();
    }

  },
  // 分数, 赏金
  initial: function () {
    // 接口
    wx.hideLoading()
  },
})