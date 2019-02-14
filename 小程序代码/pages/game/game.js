// pages/game/game.js
const app = getApp();
const innerAudioContext = wx.createInnerAudioContext();
innerAudioContext.autoplay = true;
var util = require('../../utils/util.js');
var showLd = true;
var timer = null;
var num = 10
var flag = ''
var isPlay = 0
// 游戏中可转发的次数
var nowfrequency = 0
var loading = null
var mask = true // 复活点击限制
// 记录购买复活多少次
var buy_revive_time = 0;
// 记录转发多少次
var share_revive_time = 0;
var group = [];
// 统计花多少钱  购买复活+复活卡
var money = 0
// 错误判断时间 -定时器
var delay = null;
var nopushagin = true;  //禁止用户乱点答题,true为可点
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    token: '',       //  用户登录
    rule: true,  // 规则
    num: 10,  // 数字
    sum: 0,  // 总分
    i: 1,
    koulin: [], // 口令
    targetIndex: 0, // 第几题
    gray_btn: false,
    subject: [  // 题目  记录分数
      {
        i: 0,
        sum: '',
      }, {
        i: 1,
        sum: '',
      }, {
        i: 2,
        sum: '',
      }, {
        i: 3,
        sum: '',
      }, {
        i: 4,
        sum: '',
      }
    ],
    add: false, // 加分
    add_num: 78,  // 实时分数
    end: false, // 结算
    // mark: false,  //录音中
    rule_text: '', // 规则提示
    fhmoney_txt: "",  //复活卡金额文字
    // 问答数据  
    qa: [
      // {
      //   question: "诸葛亮哪一年与刘备相见?诸葛亮哪一年与刘备相见? ",
      //   key: 1,
      //   answer: ["公元200年", "公元203年", "公元201年", "公元202年"]
      // }
    ],
    // 当前回答题目的序号
    qaTarget: 0,
    // 回答者选择答案的序号
    qaSelect: -1,
    // 回答者回答的情况 记录对错
    qaAnswer: [],
    // 答对了几道题
    qaYes: 0,
    // 挑战者次数
    frequency: 1,
    frequencyModel: false,
    // 对错
    qaShowImg: false,
    // 错误框
    qaModel: false,
    // 转发的群
    shareTickets: null,
    // 红包
    openModel: false,
    open: false, //开启
    // 可以挑战多少次
    nowfrequency: 0,
    // 现在分享挑战了几次
    now: 0,
    // 复活卡数 
    life: 0,
    // 倒计时
    load: true,
    loadend: true,
    // start: false,
    loadNum: 3,
    // 题库由谁
    foot: "若晨亲子",
    // 插入的问题
    ques: {
      id: 0,
      question: "诸葛亮哪一年与刘备相见?诸葛亮哪一年与刘备相见? ",
      key: 1,
      answer: ["公元200年", "公元203年", "公元201年", "公元202年"]
    },
    // 题目长度
    length: 0,
    // 群id
    group: [],
    // 正确答案
    qaKey: -1,
    flag2: 1, //分享按钮开关
    flag6: 1, //分享到群立即复活按钮开关

  },
  // 复活操作 
  buyLife: function () {
    if (delay) {
      clearTimeout(delay);
    }
    var life = this.data.life;
    var that = this;
    if (mask) {
      mask = false;
      if (buy_revive_time < app.globalData.buy_revive_time) {
        if (life) { // 有复活卡
          // life--;
          var postUrl = app.setConfig.url + '/index.php/api/enve/useRevive',
            postData = {
              token: that.data.token
            };
          // app.postLogin(postUrl, postData, function (res) {
          app.request(postUrl, postData, function (res) {
            if (res.data.code == 20000) {
              // console.log(res.data.life)
              app.globalData.life = parseInt(res.data.life);
              that.changeSubject(0);
              // 统计
              money++;
            } else {
              // 复活卡 0
              that.setData({
                life: 0
              })
            }
          },this)


        } else {  // 支付1元
          // console.log(util);
          var postUrl = app.setConfig.url + '/index.php/api/enve/buyRevive',
            postData = {
              token: this.data.token,
              isPlaying: 1
            };
          // app.postLogin(postUrl, postData, function (res) {
          app.request(postUrl, postData, function (res) {
            // console.log(res)
            if (res.data.code == 20000) {
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
                  that.changeSubject(0);
                  money++;
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
      }
    }
  },
  // 操作题目
  changeSubject: function (is_share) {
    wx.showToast({
      title: '复活成功',
      icon: 'success',
      duration: 1500,
      mask: true
    })
    var that = this;
    var qa = this.data.qa;
    var qaTarget = this.data.qaTarget;
    // console.log(JSON.stringify(this.data.qa))
    // 拿一道题目接口
    var postUrl = app.setConfig.url + '/index.php/api/enve/getOneQuestion',
      postData = {
        token: this.data.token,
        quests: JSON.stringify(this.data.qa)
      };
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res);
      var res = res.data.quests;
      var item = {
        id: res.id,
        question: res.quiz,
        key: res.answer,
        answer: JSON.parse(res.options)
      }
      // var ques = this.data.ques;
      qa.splice(qaTarget, 1, item);
    },this)
    // 插入的问题

    setTimeout(function () {
      // 记录已经复活 
      if (isPlay && is_share) nowfrequency++;
      that.setData({
        qa,
        qaModel: false,
        frequencyModel: false,
        qaShowImg: false,
        qaSelect: -1,
        qaKey: -1,
        num: 10,
        now: nowfrequency,
        life: app.globalData.life
      })
      // 记录
      if (is_share) {
        share_revive_time++;
      } else {
        buy_revive_time++;
      }
      // 开启复活卡;
      mask = true
      that.setTime();
    }, 1500)

  },
  // 放弃挑战 
  tolose: function () {
    if (timer) clearInterval(timer);
    wx.showLoading({
      title: '真遗憾!!',
      mask: true,
      complete: function () {
        setTimeout(function () {
          wx.hideLoading();
          wx.redirectTo({
            url: '/pages/index/index',
          })
        }, 1500)
      }
    })

  },
  closeFrequencyModel: function () {
    wx.reLaunch({
      url: '/pages/index/index',
    })
  },
  //关闭  开始游戏
  closeRule: function () {
    var frequency;
    this.setData({
      rule: false,
      right: true
    })
    // 挑战次数记录
    var postUrl = app.setConfig.url + '/index.php/api/enve/getGameFlag',
      postData = {
        token: this.data.token
      }
    var that = this;
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res)
      if (res.data.code == 20000) {
        isPlay = 1
        nowfrequency = 0
        that.loading();
      } else {
        wx.showToast({
          title: res.data.msg,
          icon: 'loading',
          mask: true,
          duration: 1500
        })
        setTimeout(function () {
          wx.reLaunch({
            url: '../index/index',
          })
        }, 1550);
      }
    },this)

  },
  // 倒计时
  loading: function () {
    var qa = this.data.qa
    if (qa.length > 0 && !this.data.rule){
      innerAudioContext.stop();
      innerAudioContext.src = '/music/timer.mp3'
      innerAudioContext.play();
      var loadNum = this.data.loadNum;
      var that = this;
      loading = setInterval(function () {
        loadNum--;
        if (loadNum == 0) {
          innerAudioContext.stop();
          innerAudioContext.src = '/music/ready_go.mp3'
          innerAudioContext.play();
          that.setData({
            loadend: false
          })
          setTimeout(function () {
            that.setData({
              load: false
            }, function () {
              that.setTime();
            })
          }, 1200)
          clearInterval(loading);
          return false;
        }
        innerAudioContext.stop();
        innerAudioContext.src = '/music/timer.mp3'
        innerAudioContext.play();
        that.setData({
          loadNum
        })
      }, 1000)
    }
  },
  // 定时器
  setTime: function () {
    if (timer) {
      nopushagin = true
      clearInterval(timer);
      this.setData({
        right: true
      })
    }
    num = 10;
    var that = this;
    timer = setInterval(function () {
      num--;
      that.setData({
        num
      })
      // 过了10秒 下一题
      if (num <= 0) {
        if (timer) {
          clearInterval(timer)
          that.setData({
            right: false
          })
        };
        wx.showLoading({
          title: '默认选第一哦!!',
          mask: true,
          success: function () {
            setTimeout(function () {
              wx.hideLoading();
              var obj = {
                currentTarget: {
                  dataset: {
                    key: ''
                  }
                }
              };
              that.selectstart(obj);
            }, 1500)
          }
        })
      }
    }, 1000);

  },
  // 回答
  selectstart: function (e) {
    var that = this;
    if (timer) {
      clearInterval(timer)
      that.setData({
        right: false
      })
    };
    var qaTarget = this.data.qaTarget,
      qaSelect;

    // 回答的序号

    var qaSelect = e.currentTarget.dataset.key || 1;
    // 答案
    var qa = this.data.qa[qaTarget].key;
    var qaAnswer = this.data.qaAnswer;
    var qaYes = this.data.qaYes;
    var flag2 = this.data.flag2;
    var flag6 = this.data.flag6;
    // 记录
    if (nopushagin){
      nopushagin = false;
      if (qaSelect == qa) { //yes
        // if (true) {
        innerAudioContext.stop();
        innerAudioContext.src = '/music/right.mp3'
        innerAudioContext.play();
        wx.showToast({
          title: '答对啦',
          icon: 'success',
          mask: true,
          duration: 1000
        })
        qaAnswer.push(1);
        qaYes++;
        this.setData({
          qaShowImg: true,
          qaSelect
        })
      } else {
        // 答错
        innerAudioContext.stop();
        innerAudioContext.src = '/music/wrong.mp3'
        innerAudioContext.play();
        // qaAnswer.push(0);
        app.globalData.success = false;
        this.setData({
          qaShowImg: false,
          qaKey: qa,
          qaSelect
        })
        wx.showToast({
          title: '答错咯',
          icon: 'loading',
          mask: true,
          duration: 3000
        })
        if (buy_revive_time >= app.globalData.buy_revive_time){
          this.setData({
            gray_btn:true,
          })
        }
        if (buy_revive_time >= app.globalData.buy_revive_time && share_revive_time >= app.globalData.share_revive_time) {
          wx.showToast({
            title: '挑战失败',
            icon: 'loading',
            mask: true,
            duration: 3000
          })
          setTimeout(function () {
            that.setQaModel()
          }, 2800)
          return false;
        }
        // if (flag2 || flag6) {
        //   that.setQaModel()
        //   return false;
        // }
        this.setData({
          qaShowImg: false,
          qaKey: qa,
          qaSelect,
          
        })
        setTimeout(function(){
          if (flag2 || flag6){
            that.setData({
              qaModel: true
            })
          }
          else{
            that.setQaModel();
          }
        },3000)
        delay = setTimeout(function () {
          that.setData({
            qaModel: false
          })
          that.setQaModel()
        }, 12000)
        return false
      }
      // 第六题
      if (qaTarget == that.data.length-1) {
        app.globalData.qaAnswer = qaAnswer;
        app.globalData.qaYes = qaYes;
        app.globalData.success = true;
        // 开启红包
        if (qaYes == that.data.length) { // 全对
          isPlay = 0;
          wx.showToast({
            title: '娃娃在来的路上',
            icon: 'success',
            mask: true,
            duration: 1500
          })
          // 获取赏金
          setTimeout(function () {
            that.open();
          }, 1200)
          return false;
        }
      } else {
        qaTarget++;
      }
    }
    setTimeout(function () {
      that.setData({
        qaAnswer,
        qaYes,
        qaTarget,
        qaSelect: -1,
        qaKey: -1,
        num: 10
      }, function () {
        that.setTime();
      })
    }, 1000);

  },
  // 成功, 开包
  open: function () {
    innerAudioContext.stop();
    innerAudioContext.src = '/music/succeed.mp3'
    innerAudioContext.play();
    this.toflag(1);
  },
  // 挑战失败
  setQaModel: function () {

    if (delay) {
      clearTimeout(delay);
    }
    innerAudioContext.stop();
    innerAudioContext.src = '/music/fail.mp3'
    innerAudioContext.play();
    this.toflag(0);
    wx.redirectTo({
      url: '../complete/complete',
    })
  },
  toflag: function (is_pass) {
    var postUrl = app.setConfig.url + '/index.php/api/enve/dealResult',
      postData = {
        token: this.data.token,
        // flag: flag,
        is_pass: is_pass,
        money: money,  // 购买复活+复活卡
        share_revive_time: share_revive_time
      }
    var that = this;
    app.request(postUrl, postData, function (res) {
      if (res.data.code === 20000) {
        if (is_pass == 1) {
          app.globalData.endPrize = res.data.prize;
          app.globalData.endPrize.src = app.setConfig.url + '/' + app.globalData.endPrize.src;
          wx.redirectTo({
            url: '../complete/complete?challengeId=' + res.data.challengeId + '&src=' + res.data.prize.src + '&doll_name=' + res.data.prize.text,
          })
        }
      }
    },this)
  },

  // 转发
  onShareAppMessage: function (res) {
    if(delay) {
      clearTimeout(delay);
    }
    wx.showShareMenu({
      withShareTicket: true
    })

    // title = this.data.userInfo.nickName + ' ' + this.data.relay;
    var that = this;
    if (res.from === 'button') {
      // 来自页面内转发按钮
      // console.log(res.target)
    }
    return {
      title: app.globalData.title,
      path: '/pages/index/index',
      imageUrl: '/images/share.png',
      success: function (res) {

        if (share_revive_time >= app.globalData.share_revive_time) {
          wx.showToast({
            title: '没有转发机会',
            icon: 'none',
            mask: true,
            duration: 1500
          })
          return false;
        }

        //var shareTickets = res.shareTickets[0];
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
        // console.log(platform);
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
              // console.log('只能是群聊哦');
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
        // console.log(res)
      }
    }
  },
  shareJudge: function (shareTickets, encryptedData, iv) {
    wx.showLoading({
      title: '验证中..',
      mask: true
    })
    var that = this;
    var postUrl = app.setConfig.url + '/index.php/api/enve/shareGetTimes',
      postData = {
        token: app.globalData.token,
        isPlaying: isPlay,
        shareTicket: shareTickets,
        encryptedData: encryptedData,
        iv: iv,
        isgroup: 0
      };
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res);
      if (res.data.code == 20000) {

        if (!group.find(function(value){
          return value == res.data.gid;
        })) {
          // 添加群id
          group.push(res.data.gid);
          wx.showToast({
            title: '转发成功',
            icon: 'success',
            duration: 1500,
            mask: true
          })
          // 记录次数
          share_revive_time++;
          // 转发
          that.changeSubject(1);
        } else {
          wx.showToast({
            title: '要不同的群哦',
            icon: 'loading',
            duration: 1500,
            mask: true
          })
        }
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
  },

  onLoad: function () {
    app.userLogin(this);
    // flag = ''
    wx.showLoading({
      title: '加载中•••',
    })
    // var scene = decodeURIComponent(options.scene);
    // if (scene > 0) {
    //   var pid = scene;
    // } else {
    //   var pid = options.pid;
    // }
    // var pid = options.pid;
    var that = this;
    this.setData({
      ruleText: app.globalData.ruleText,
      life: app.globalData.life
    })
    // 注释
    this.loop();
  },
  loop: function () {
    if (!app.globalData.token) {
      var that = this
      setTimeout(function () { that.loop(); }, 100)
    } else {
      var info = app.globalData.userInfo,
        tok = app.globalData.token,
        flag2 = app.globalData.flag2,
        flag6 = app.globalData.flag6,
        revive_money = app.globalData.revive_money,
        fhmoney_txt = "支付" + revive_money+"元立即复活";
      if (!info) {
        app.userInfoReadyCallback = res => {
          this.setData({
            userInfo: res.userInfo,
            token: tok,
            flag2: flag2,
            flag6: flag6,
            fhmoney_txt: fhmoney_txt,
          })
        }
      } else {
        this.setData({
          userInfo: info,
          token: tok,
          flag2: flag2,
          flag6: flag6,
          fhmoney_txt: fhmoney_txt
        })
      }
      this.initial();
    }

  },
  // 离开界面
  onUnload: function () {
    clearInterval(timer);
    timer = null;
  },
  // 题目引入
  initial: function () {
    // 接口
    wx.hideLoading()
    var postUrl = app.setConfig.url + '/index.php/api/enve/startGame',
      postData = {
        token: this.data.token
      }
    var that = this;
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      // console.log(res)
      if (res.data.code == 20000) {
        let data = res.data.quests
        let qa = []
        for (let i = 0; i < data.length; i++) {
          let item = {
            id: data[i].id,
            question: data[i].quiz,
            key: data[i].answer,  // data[i].answer
            answer: JSON.parse(data[i].options)
          }
          qa.push(item)
        }
        var length = data.length;
        // console.log(qa)
        that.setData({
          qa,
          thisImg: app.globalData.thisImg,
          nowfrequency: app.globalData.nowfrequency,
          length
        })
        that.loading();
      }
    },this)
  }
})