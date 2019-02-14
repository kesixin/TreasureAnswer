// pages/ranking/ranking.js
//获取应用实例
const app = getApp();
var group = '';
Page({
  data: {
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    page: 0,
    lower: true,
    list: [
      
    ],
    mylist: {
      // idx:122,
      // user_name: "王者大神",    // 昵称
      // head_img: "https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eq4Jsic8dPLibDhG0U6QHsSU5rsZriaWvCzsq3Cvic92lyntBepemiaM2hlXdjT3MTtrkhnbR2CIBb118A/0",     //  头像
      // sendNum: "894950",          // 发出的个数
      // score: 490,   // 最高分
      // amount: 33.32  // 领取金额
      // doll:    //多少个娃娃
    },
    // current
    mode: 0,
    group: true,
    // 总次数
    total: 23414,
    // 标题
    right: '答对12题随机送娃娃',
  },

  onReady: function (options) {
    // 群内智力
    // console.log(options)
    if (options) group = options.group || 0;

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
      mask: true
    })
  },
  onLoad: function () {
    app.userLogin(this);
    wx.showLoading({
      title: '加载中•••'
    })
    if (!app.globalData.token ) {
      var that = this
      app.userInfoReadyCallback = function () {
        that.loop();
      }
    } else {

      this.loop();
      wx.hideLoading()
    }
  },
  loop: function () {
    if (!app.globalData.encryptedData ) {
      var that = this
      app.allDataReadyCallback = function () {
        that.loaddata();
        // wx.hideLoading()
      }
    } else {
      
      this.loaddata();
      // wx.hideLoading()
    }
  },
  //数据加载
  loaddata: function () {
    var info = app.globalData.userInfo,
      tok = app.globalData.token;
    this.setData({
      userInfo: info,
      token: tok,
      hasUserInfo: true
    })
    
    if (!this.data.lower) { return false; }
    wx.showLoading({
      title: '加载中•••'
    })
    this.data.lower = false
    var tok = this.data.token;
    var postUrl = app.setConfig.url + '/index.php/api/enve/rankInGroup';
    
    var pag = this.data.page + 1;
    var postData = {
      page: pag,
      token: tok,
      inRankPage: 1,
      encryptedData: app.globalData.encryptedData,
      iv: app.globalData.iv,
    };
    // console.log(postData)
    // console.log('ok post')
    // app.postLogin(postUrl, postData, this.initial);
    app.request(postUrl, postData, this.initial,this);
  },

  // 获取排行榜信息
  initial: function (res) {
    // console.log(res)
    if (res.data.code == 20000) {
      wx.hideLoading()
      var datas = res.data;
      // console.log(datas)
      if (datas.data.rankList.length == 0) {
        this.setData({
          page: -1
        })
        return false;
      }
      var page = this.data.page + 1;
      var list = page == 1 ? [] : this.data.list;
      list = list.concat(datas.data.rankList);
      // var mylist = datas.my;
      var total = datas.data.total ? datas.data.total : "110";
      var right = datas.data.right ? datas.data.right : '答对12题随机送娃娃';
      this.setData({
        list: list,
        // mylist: mylist,
        page: page,
        lower: true,
        thisImg: app.globalData.thisImg,
        total: total,
        right: right,
      })
    }
    wx.stopPullDownRefresh();
  },
  // 触底翻页
  lower: function () {
    this.loaddata();
  },
  // 下拉刷新
  onPullDownRefresh: function () {
    this.setData({
      list: [],
      page: 0,
      lower: true
    })
    this.lower();
  },
  // 转发
  onShareAppMessage: function (res) {

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
      title: '群内智力大比拼啦, 来测下你的智力吧!',
      path: '/pages/group/group?group',
      imageUrl: '/images/share.png',
      success: function (res) {
        // console.log(res.shareTickets)
        var shareTickets = res.shareTickets[0];
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
              title: '只能转发群',
              icon: 'loading',
              duration: 1500,
              mask: true
            })
          } else {
            wx.getShareInfo({
              shareTicket: shareTickets,
              success: function (res) { //是群聊
                // console.log(res.encryptedData)
                // that.shareJudge(shareTickets, res.encryptedData, res.iv);
                that.shareqpm(res.encryptedData, res.iv);
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
              // that.shareJudge(shareTickets, res.encryptedData, res.iv);
              that.shareqpm(res.encryptedData, res.iv);
            },
            fail: function () {
              wx.showToast({
                title: '只能转发群',
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
  shareqpm: function (encryptedData, iv) {
    // wx.showLoading({
    //   title: '验证中..',
    //   mark: true
    // })
    var tok = this.data.token;
    var postUrl = app.setConfig.url + '/index.php/api/enve/rankInGroup';
    app.globalData.encryptedData = encryptedData;
    app.globalData.iv = iv;
    var postData = {
      page: 1,
      token: tok,
      inRankPage: 1,
      encryptedData: encryptedData,
      iv: iv,
    };
    // console.log(postData)
    // console.log('ok post')
    var that = this;
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      if (res.data.code == 20000) {
        wx.hideLoading()
        var datas = res.data;
        console.log(datas)
        if (datas.data.rankList.length == 0) {
          that.setData({
            page: -1
          })
          return false;
        }
        var list = datas.data.rankList;
        that.setData({
          list: list,
          page: 1,
          lower: true,
          thisImg: app.globalData.thisImg,
        })
      }
    },this)
  },
  toindex: function(){
    wx.redirectTo({
      url: '../index/index',
    })
  }
})