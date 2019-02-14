// pages/certificate/certificate.js
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},   // 用户信息
    
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.loop();
  },

  loop: function () {
    if (!app.globalData.token) {
      var that = this
      setTimeout(function () { that.loop(); }, 100)
    } else {
      var info = app.globalData.userInfo,
        tok = app.globalData.token;
        // pid = app.globalData.pid;
      if (!info) {
        app.userInfoReadyCallback = res => {
          this.setData({
            userInfo: res.userInfo,
            token: tok
          })
        }
      } else {
        // console.log(info);
        wx.showLoading({
          title: '证书生成中',
          mask: true
        })
        this.setData({
          userInfo: info,
          token: tok,
          // pid
        })
        var postData = {
          token: tok,
          // pid
        }
        var postUrl = app.setConfig + "";
        app.postLogin(postUrl, postData, this.initial);
      }
      
    }

  },
  initial: function (res) {
    if (res.data.code == 20000) {
      wx.hideLoading()
      this.setData({

      })
    }
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})