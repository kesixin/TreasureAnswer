//app.js
var md5 = require('utils/md5.js')
var wxTools = require('utils/wxTools.js')
var loginInfo = {};

App({
  setConfig: {
    url: 'https://www.hhyxx.cn/',
    hb_appid: 'wx087ce1d12cb37296',
    hb_appsecret: 'd5ede221af6372e2d8115d93c4b01fa7'
  },
  onLaunch: function () {
    //this.userLogin();
  },
  onShow: function (options){
    this.onShareTicket(options)
  },
  
  globalData: {
    userInfo: null,
    token: '',
    timer: null,
    excAmount: null,  //可兑换的挑战次数
    revive_money: null,  //复活卡金额
    toAdvs: true,
    title: '究竟谁是答题王, 等你来挑战! !',
    iv:'',
    encryptedData:'',
    // thisImg: "/images/wd/bg.png"
  },
  getSign: function () {
    var timestamp = Math.round(new Date().getTime() / 1000);
    var sign = md5.md5(this.setConfig.hb_appid + this.setConfig.hb_appsecret + timestamp);
    sign = md5.md5(sign + this.setConfig.hb_appsecret);
    return { appid: this.setConfig.hb_appid, timestamp: timestamp, sign: sign };
  },
  //登录
  userLogin: function (page) {
    if (this.globalData.token){
      return false;
    }
    var that = this;
    var codes;
    //获取登录code
    wx.login({
      success: function (res) {
        //console.log(res.code);return false;
        if (res.code) {
          loginInfo.code = res.code;
          codes = res.code;
          //获取用户信息
          wx.getSetting({
            success: res => {
              if (res.authSetting['scope.userInfo']) {
                // 已经授权，可以直接调用 getUserInfo 获取头像昵称，不会弹框
                wx.getUserInfo({
                  success: res => {
                    // 可以将 res 发送给后台解码出 unionId
                    var infoUser = '';
                    that.globalData.userInfo = infoUser = res.userInfo;
                    // 所以此处加入 callback 以防止这种情况 获取慢
                    if (that.userInfoReadyCallback) {
                      that.userInfoReadyCallback(res)
                    }
                    //用户信息入库
                    var url = that.setConfig.url + '/index.php/User/login/dologin';
                    var data = {
                      user_name: infoUser.nickName,
                      nick_name: infoUser.nickName,
                      head_img: infoUser.avatarUrl,
                      sex: infoUser.gender,
                      coutry: infoUser.country,
                      city: infoUser.city,
                      province: infoUser.province,
                      code: codes,
                    }
                    //that.postLogin(url, data);
                    that.request(url, data, (res) => {
                      // console.log(res)
                      if (res.data.code != 20000) {
                        wx.showToast({
                          title: res.data.msg,
                          icon: 'loading',
                          mask: true,
                          duration: 1500
                        })
                        if (res.data.code == 40500) {
                          wx.showToast({
                            title: res.data.msg,
                            icon: 'loading',
                            mask: true,
                            duration: 1500
                          })
                        }
                        return false;
                      }
                      if (res.data.token) {
                        that.globalData.token = res.data.token;
                        if (that.tokenReadyCallback) {
                          that.tokenReadyCallback();
                        }
                      }
                    },page);
                  }
                })
              } else {
                wx.authorize({
                  scope: 'scope.userInfo',
                  success: res => {
                    //用户已经同意小程序授权
                    wx.getUserInfo({
                      success: res => {
                        // 可以将 res 发送给后台解码出 unionId
                        var infoUser = '';
                        that.globalData.userInfo = infoUser = res.userInfo;
                        // 所以此处加入 callback 以防止这种情况
                        if (that.userInfoReadyCallback) {
                          that.userInfoReadyCallback(res)
                        }
                        //用户信息入库
                        var url = that.setConfig.url + '/index.php/User/login/dologin';
                        var data = {
                          user_name: infoUser.nickName,
                          nick_name: infoUser.nickName,
                          head_img: infoUser.avatarUrl,
                          sex: infoUser.gender,
                          coutry: infoUser.country,
                          city: infoUser.city,
                          province: infoUser.province,
                          code: codes,
                          // 
                          encryptedData: res.encryptedData,
                          iv: res.iv,
                        }
                        //that.postLogin(url, data);
                        that.request(url, data,function(res){
                          if (res.data.code != 20000) {
                            wx.showToast({
                              title: res.data.msg,
                              icon: 'loading',
                              mask: true,
                              duration: 1500
                            })
                            if (res.data.code == 40500) { 
                              wx.showToast({
                                title: res.data.msg,
                                icon: 'loading',
                                mask: true,
                                duration: 1500
                              })
                             }
                            return false;
                          }
                          if (res.data.token) {
                            that.globalData.token = res.data.token;
                            if (that.tokenReadyCallback) {
                              that.tokenReadyCallback();
                            }
                          }
                        },page);
                      }
                    })
                  }
                })
              }
            }
          });
        } else {
          that.userLogin();
          return false;
        }
      }
    })
  },
  request: function (url, data, cb, page){
    var signData = this.getSign();
    data.sign = signData.sign;
    data.timestamp = signData.timestamp;
    wxTools.wxRequest({ url: url, data: data, method: 'post' }, cb, page, 0);
  },
  //提交
  postLogin: function (url, data, callback = function () { }) {
    var that = this;
    var signData = this.getSign();
    data.sign = signData.sign;
    data.timestamp = signData.timestamp;
    //发起网络请求
    wx.request({
      url: url,
      data: data,
      method: 'POST',
      header: { 'content-type': 'application/x-www-form-urlencoded' },
      success: function (res) {
        if (res.data.code != 20000) {
          wx.showToast({
            title: res.data.msg,
            icon: 'loading',
            mask: true,
            duration: 1500
          })
          if (res.data.code == 40500) { callback(res); }
          return false;
        }
        if (res.data.token) {
          that.globalData.token = res.data.token;
          if (that.tokenReadyCallback) {
            that.tokenReadyCallback();
          }
        }
        //console.log(that.globalData)
        callback(res);

      }
    })
  },
  onShareTicket: function (options) {
    // console.log("shareTicket: "+options.shareTicket);
    var that = this;
    // console.log('111:'+options)
    // console.log(options)
    if (options.shareTicket) {
      var shareTickets = options.shareTicket
      wx.getShareInfo({
        shareTicket: shareTickets,
        success: function (res) { //是群聊
          that.globalData.encryptedData = res.encryptedData;
          that.globalData.iv = res.iv;
          if (that.allDataReadyCallback && that.globalData.token){
            that.allDataReadyCallback();
          }
        }
      })
      
    }
    
  }
})
