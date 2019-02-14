//help.js
const app = getApp()

Page({
  data: {
    lists: [
      // {
      //   tit:"语音口令怎么玩？",
      //   txt:"您可以设置一个带奖励的语音口令或问题，好友说对口令或者答对问题才能领取到奖励。"
      // }, {
      //   tit: "我支付了但没有发出去？",
      //   txt: "请在主页的【我的记录】中找到相应的记录，点击进入详情后点击【去转发】可把口令转发给好友或群，您也可以生成朋友圈分享图后发朋友圈。"
      // }, {
      //   tit: "好友可以转发我的口令吗？",
      //   txt: "可以的，您分享给好友或者转发到微信群的语音口令，其他好友均可再次转发。"
      // }, {
      //   tit: "发口令会收取服务费吗？",
      //   txt: "发语音口令会收取1%的服务费。"
      // }, {
      //   tit: "未领取的金额会怎样处理？",
      //   txt: "未领取的金额将于24小时后退至小程序余额。"
      // }, {
      //   tit: "如何提现到微信钱包？",
      //   txt: "在主页的【余额提现】或详情页的【去提现】均可跳转至余额提现页面进行提现，提现金额每次至少1元。"
      // }, {
      //   tit: "提现会收取服务费吗？多久到账？",
      //   txt: "提现不收取服务费；申请提现后会在1-5个工作日内转账到您的微信钱包。"
      // }
    ],
    control:-1,
    version:'1.0.0',
  },
  onshow: function(e){
    if (this.data.control === e.target.dataset.id){
      this.setData({
        control: -1
      })
    }else{
      this.setData({
        control: e.target.dataset.id
      })
    }
  },
  
  onLoad: function () {
    var res = wx.getSystemInfoSync();
    this.getlist()
  },

  getlist: function () {
    var postUrl = app.setConfig.url + '/index.php/Api/Enve/getFAQ',
      postData = {
        token: app.globalData.token,
      };
    var that = this
    // app.postLogin(postUrl, postData, function (res) {
    app.request(postUrl, postData, function (res) {
      if (res.data.code == 20000) {
        that.setData({
          lists: res.data.faqList
        })
      }
    },this)
  }
})
