//home.js
//获取应用实例
var app = getApp();
var timehome = null
Page({
  data: {
    userInfo:{},
    hasUserInfo:false,
    token:'',
    userName:'夺宝答题王',
    display:"../../images/display.png",
    Tel:'020-22096568',
    date: ''
  },
  
  //电话
  bindViewTel: function () {
    wx.makePhoneCall({
      phoneNumber: this.data.Tel,
    })
  },
  //右上角添加转发按钮
  onShareAppMessage: function (res) {
    return {
      success: function (res) {
        // 转发成功
      },
      fail: function (res) {
        // 转发失败
      }
    }
  },
  onReady: function () {
    var that = this;
    timehome = setTimeout(function () {
      that.loop();
    }, 1500)
    var aa = 1524150357000;
    var tims = new Date().getTime();
    if (tims > aa) {
      wx.reLaunch({
        url: '../index/index'
      })
      if (timehome) { clearTimeout(timehome); }
      return false
    }
    wx.showLoading({
      title: '加载中•••',
      mask: true
    })
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          versionnum: res.SDKVersion,
        })
      }
    })
  },
  
  loop: function () {
    var info = app.globalData.userInfo;
      // tok = app.globalData.token;
      wx.setNavigationBarTitle({
        title: this.data.userName
      })
      this.setData({
        userInfo: info,
        hasUserInfo: true
      })
      wx.hideLoading()
    
  }
})
