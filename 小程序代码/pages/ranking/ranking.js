// pages/ranking/ranking.js
//获取应用实例
const app = getApp();
var group = '';
var lower = true
Page({
  data: {
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    page: 0,
    lower: true,
    list: [],
    list1: [],
    myPrizeAmount: 0,
    myChallengeTimes: 0,
    myrank1:0,
    myrank2:0,
    mylist: {
      idx: 122,
      user_name: "王者大神",    // 昵称
      head_img: "https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eq4Jsic8dPLibDhG0U6QHsSU5rsZriaWvCzsq3Cvic92lyntBepemiaM2hlXdjT3MTtrkhnbR2CIBb118A/0",     //  头像
      sendNum: "894950",          // 发出的个数
      score: 490,   // 最高分
      amount: 33.32,  // 领取金额
      doll: 0    //多少个娃娃
    },
    // current
    mode: 0,
    group: true,
    // 总次数
    total: 23414,
    // 标题
    right: '答对12题随机送娃娃',
    page1: 1, // 智力榜
    page2: 1,  // 毅力榜
    flag3: 1, //群内智力开关
  },
  lower1: function () {
    if (lower) {
      lower = false;

      var that = this;
      var page1 = this.data.page1;
      page1++;
      
      var postUrl = app.setConfig.url + '/index.php/api/enve/getRankData';
      var postData = {
        token: app.globalData.token,
        page: page1,
        type:1
      };
      // app.postLogin(postUrl, postData, function (res) {
      app.request(postUrl, postData, function (res) {
        that.initial(res);
        lower = true;
      },this);
    }
  },
  lower2: function () {
    if (lower) {
      lower = false;

      var that = this;
      var page2 = this.data.page2;
      page2++;
      var postUrl = app.setConfig.url + '/index.php/api/enve/getRankData';
      var postData = {
        token: app.globalData.token,
        page: page2,
        type:2
      };
      // app.postLogin(postUrl, postData, function (res) {
      app.request(postUrl, postData, function (res) {
        that.initial(res);
        lower = true;
      },this);
    }
  },

  bindselect: function (e) {
    // console.log(current)
    var current = e.currentTarget.dataset.current;
    this.setData({
      mode: current
    })
  },

  bindchange: function (e) {
    // console.log(e)
    var current = e.detail.current;
    this.setData({
      mode: current
    })
  },

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
      title: '群内智力大比拼啦, 来测下你的智力吧!',
      path: '/pages/group/group?group=1',
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
                // that.shareJudge(shareTickets, res.encryptedData, res.iv);
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
    // wx.showLoading({
    //   title: '验证中..',
    //   mark: true
    // })
    var that = this;
    var frequency = this.data.frequency;
    var postUrl = app.setConfig.url + '/index.php/api/enve/shareGetTimes',
      postData = {
        token: app.globalData.token,
        isPlaying: 0,
        shareTicket: shareTickets,
        encryptedData: encryptedData,
        iv: iv,
        isgroup: 1 // 是群内转发
      };
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res);
      // if (res.data.code == 20000) {
      //   wx.showToast({
      //     title: '转发成功',
      //     icon: 'success',
      //     duration: 1500,
      //     mask: true
      //   })
      //   frequency++;
      //   setTimeout(function () {
      //     that.setData({
      //       frequency
      //     })
      //   }, 1500)
      // } else {
      //   wx.showToast({
      //     title: '要不同的群哦',
      //     icon: 'loading',
      //     duration: 1500,
      //     mask: true
      //   })
      // }
    },this)
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
    this.loop()
  },
  loop: function () {
    if (!app.globalData.token) {
      var that = this
      setTimeout(function () { that.loop(); }, 100)
    } else {
      var info = app.globalData.userInfo,
        tok = app.globalData.token,
        flag3 = app.globalData.flag3;
      if (info) {
        this.setData({
          userInfo: info,
          token: tok,
          hasUserInfo: true,
          flag3: flag3
        })
      }
      this.loaddata();
      wx.hideLoading()
    }
  },
  //数据加载
  loaddata: function () {
    if (!this.data.lower) { return false; }
    wx.showLoading({
      title: '加载中•••'
    })
    this.data.lower = false
    var tok = this.data.token;
    var postUrl = app.setConfig.url + '/index.php/api/enve/getRankData';

    // var pag = this.data.page + 1;
    var postData = {
      page: this.data.page1,
      token: tok
    };
    // app.postLogin(postUrl, postData, this.initial);
    app.request(postUrl, postData, this.initial,this);
  },

  // 获取排行榜信息
  initial: function (res) {
    if (res.data.code == 20000) {
      wx.hideLoading()
      var datas = res.data;
      // console.log(datas)

      if (datas.page == 1){
        // console.log(datas.myRank.rank1)
        this.setData({
          list: datas.data.list1,
          list1: datas.data.list2,
          myPrizeAmount: datas.myPrizeAmount,
          myChallengeTimes: datas.challengeTimes,
          myrank1: datas.myRank.rank1,
          myrank2: datas.myRank.rank2,
          lower: true,
          thisImg: app.globalData.thisImg
        })
      }else{
        if(datas.data.length != 0){
          if (datas.type == 1) {
            var list = this.data.list;
            list = list.concat(datas.data);
            var page1 = this.data.page1 + 1;
            this.setData({
              list: list,
              page1: page1
            })
          } else {
            var list1 = this.data.list1;
            list1 = list1.concat(datas.data);
            var page2 = this.data.page2 + 1;
            this.setData({
              list1: list1,
              page2: page2
            })
          }
        }
        
        
      }

      // var list = page == 1 ? [] : this.data.list;
      // list = list.concat(datas.data);
      // var mylist = datas.my;
      // this.setData({
      //   list: list,
      //   mylist: mylist,
      //   lower: true,
      //   thisImg: app.globalData.thisImg
      // })

    }
    wx.stopPullDownRefresh();
  },
  // 触底翻页
  // lower:function(){
  //   this.loaddata();
  // },
  // 下拉刷新
  onPullDownRefresh: function () {
    this.setData({
      list: [],
      list1: [],
      page1: 1,
      page2: 1,
      lower: true
    })
    //this.lower();
    this.loaddata();
  }
})