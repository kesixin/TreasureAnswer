// pages/prizeList/prizelist.js
const app = getApp();
var mask = true  // 防止多次点击

Page({
  /**
   * 页面的初始数据
   */
  data: {
    mode:0,
    excAmount: null,  //可兑换的挑战次数
    // 娃娃奖品
    prize: [],

    // 领取记录
    prizeList: [
      // {
      //   id:1,
      //   create_time: "2018-03-13 15:21",
      //   head_img: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   prize_img: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   nick_name: "- _ 24",
      //   prize_name: '泰迪熊',
      //   status: 0 //未兑换
      // },
      // {
      //   id: 3,
      //   create_time: "2018-03-13 15:21",
      //   head_img: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   prize_img: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   nick_name: "- _ 24",
      //   prize_name: '邦尼兔',
      //   status: 1 //兑换了娃娃
      // },
      // {
      //   id: 3,
      //   create_time: "2018-03-13 15:21",
      //   head_img: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   prize_img: "https://wx.qlogo.cn/mmopen/vi_32/uDYY9kUb9RuuBcqicgVakG4MiaZTGp08LTqpqUU20J30IW7dcCQQN4146MxmnfmBSLgzCrZzibicHaE60gy7ibUGGibA/0",
      //   nick_name: "- _ 24",
      //   prize_name: '邦尼兔',
      //   status: 2 //兑换了机会
      // }
    ],
    toprize:{
      // id:"1",
      // prize_img: '/images/db/index/prize1.png',
      // prize_name: '泰迪熊'

    },  //点去兑换,显示的娃娃信息

    // 获取到领取奖品列表,并且长度为空控制器
    non_dataPrize:false,
    //挑战记录id
    challengeId: 0,
    // 弹出
    exchange: false,
    // 物流
    logistics: false,

    // 填写完物流信息显示的弹框
    okexchange:false,
    app_name:"智汇酷玩",
    lower0: true, //娃娃奖品上滑触底加载控制器
    lower1: true, //领取记录上滑触底加载控制器
    page0:0,    //娃娃奖品分页
    page1:0,    //领取记录分页
  },

  /**
   * 生命周期函数--监听页面加载
   */

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

  onLoad: function (e) {
    app.userLogin(this);
    wx.showLoading({
      title: '加载中•••'
    })
    if (e.mode == 1){
      this.setData({
        mode: e.mode
      })
    }
    this.loop()
  },
  loop: function () {
    if (!app.globalData.token) {
      var that = this
      setTimeout(function () { that.loop(); }, 100)
    } else {
      var info = app.globalData.userInfo,
        tok = app.globalData.token,
        excAmount = app.globalData.excAmount
      if (info) {
        this.setData({
          userInfo: info,
          token: tok,
          hasUserInfo: true,
          excAmount: excAmount
        })
      }
      //this.loaddata();
      if(this.data.mode == 0){
        this.alldoll();
      }
      else {
        this.dhprize();
      }
      wx.hideLoading()
    }
  },

  bindselect: function (e) {
    // console.log(current)
    var current = e.currentTarget.dataset.current;
    this.setData({
      mode: current
    })
    if (current == 0 && this.data.page0 == 0) {
      // console.log(this.data.page0)
      this.alldoll();
    }
    else if (current == 1 && this.data.page1 == 0) {
      this.dhprize();
    }
  },
  bindchange: function (e) {
    // console.log(e)
    var cur = e.detail.current;
    if (this.data.mode == cur) {
      return false;
    }
    else {
      this.setData({
        mode: cur
      })
    }

  },

  // 触底加载器
  lower: function(e){
    var i = e.currentTarget.dataset.current;
    // console.log(this.data.lower0)
    if (this.data.lower0 && i == 0){
      wx.showLoading({
        title: '加载中•••',
        mask: true
      })
      this.setData({
        lower0: false
      })
      this.alldoll(); 
    }
    if (this.data.lower1 && i == 1){
      wx.showLoading({
        title: '加载中•••',
        mask: true
      })
      this.setData({
        lower1: false
      })
      this.dhprize(); 
    }
  },

  // 所有娃娃奖品列表
  alldoll: function (){
    var page0 = this.data.page0+1;
    var that = this;
    var postUrl = app.setConfig.url + '/index.php?g=Api&m=Enve&a=prizelist',
        postData = {
          token: app.globalData.token,
          page: page0
        };
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res){
      wx.hideLoading()
      if (res.data.code == 20000) {
        // console.log(res.data)
        var prize = page0 == 1 ? [] : this.data.prize;
        var newprize = res.data.data;
        prize = prize.concat(newprize)
        if (newprize.length == 0){
          that.setData({
            page0:-1
          })
          return false;
        }
        else{
          that.setData({
            prize: prize,
            page0: page0,
            lower0:true
          })
          // console.log(that.data.page0)
        }
      }
    },this)
  },

  // 领取记录列表
  dhprize: function(page){
    var that = this;
    var page1 = page ? page : this.data.page1+1;
    var postUrl = app.setConfig.url + '/index.php/api/enve/prizeRecord',
      postData = {
        token: app.globalData.token,
        page: page1
      };
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
    // console.log(111)
      wx.hideLoading()
      if (res.data.code == 20000) {
        var list = page1 == 1 ? [] : that.data.prizeList;
        // var list = that.data.prizeList;
        var newlist = res.data.data.prizeList;
            list = list.concat(newlist);
         if (newlist.length == 0) {
          that.setData({
            page1: -1,
            non_dataPrize: true
          })
          return false;
        }
        else{
          that.setData({
            prizeList: list,
            lower1: true,
            page1: page1,
          })
        }
      }
    },this)
  },


  // 弹出
  toExchange: function (e) {
    var id = e.target.dataset.id
    this.setData({
      exchange: true,
      challengeId: id
    })
    //console.log(id)
  },
  // 关闭
  closeModel: function () {
    this.setData({
      exchange: false
    })
  },

  // 兑换娃娃 收集信息
  tologistics: function () {
    var exchange = this.data.exchange;
    var list = this.data.prizeList;
    for (var i = 0; i < list.length; i++){
      if(list[i].id){
        var clicklist = list[i];
        this.setData({
          toprize: clicklist
        })
      }
      // console.log(clicklist)
    }
    this.setData({
      exchange: false,
      logistics: true
    })
  },
  // 兑换挑战机会
  tochance: function () {
    wx.showLoading({
      title: '兑换中',
      mask: true
    })
    var postUrl = app.setConfig.url + '/index.php/api/enve/exchangeChallengeTimes',
      postData = {
        token: app.globalData.token,
        challengeId: this.data.challengeId //兑换的id
      }
    var that = this;
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      var title = '兑换失败';
      if (res.data.code == 20000) {
        title = '兑换成功';
        app.globalData.frequency = res.data.ctime;
        // console.log(app.globalData.frequency)
        that.setData({
          exchange: false,
          page: 0
        })
      }
      wx.showLoading({
        title: title,
        mask: true
      })
      setTimeout(function () {
        wx.hideLoading();
        // that.loaddata();
        that.dhprize(1);
      }, 1500)
    },this)
  },
  // 地址
  submit: function (e) {
    var toprize = this.data.toprize;
    var phone = e.detail.value.phone;
    var re = /^(13[0-9]|14[0-9]|15[0-9]|18[0-9])\d{8}$/;
    var retu = phone.match(re);
    if (mask) {
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
      else{
        mask = false
        wx.showLoading({
          title: '提交中',
          mask: true
        })
        // return false
      }
      /*
      info: {
        prize_id:
        name: 
        phone: 
        address:
      }
      
      */
      var postUrl = app.setConfig.url + "/index.php/api/enve/saveExpressInfo",
        postData = {
          token: app.globalData.token,
          challengeId: this.data.challengeId,
          name: e.detail.value.name,
          phone: e.detail.value.phone,
          address: e.detail.value.address,
          // prize_id: toprize.id
        };
      var that = this;
      // app.postLogin(postUrl, postData, function (res) {
      app.request(postUrl, postData, function (res) {
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
          that.setData({
            okexchange:true
          })
          // wx.showLoading({
          //   title: '已成功兑换娃娃',
          //   mask: true
          // })
          setTimeout(function () {
            wx.hideLoading();
            that.setData({
              logistics: false,
              exchange: false,
              page: 0
            })
            // that.loaddata();
            that.data.dhprize(1);
          }, 1500)
        }
      },this)
      // wx.hideLoading()
    }

  },

  toindex: function(){
    wx.redirectTo({
      url: '../index/index',
    })
  },

  back: function(){
    this.setData({
      okexchange:false
    })
    wx.redirectTo({
      url: '../prizeList/prizelist'
    })
  }
})