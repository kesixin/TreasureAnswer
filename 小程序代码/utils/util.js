const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}
let payment = (res,callback) => {
  // var payInfo = res.data.data;
  // console.log(res)
  var status;
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
      status = true;
      return true;
    },
    'fail': function(res){
      wx.showToast({
        title: '支付失败',
        icon: 'none',
        duration: 1000
      })
      status = false;
      return false;
    }
  })
  return status;
}

module.exports = {
  formatTime: formatTime,
  payment: payment
}
