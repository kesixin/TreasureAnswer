function wxRequest(obj, cb, page, type) {
  // console.log("???")
  var isOne = true
  var cachFn = function () {
    wx.request({
      url: obj.url,
      data: obj.data || {},
      header: { 'content-type': 'application/x-www-form-urlencoded' },
      method: obj.method || 'GET',
      success: function (res) {
        // console.log(res)
        cb.call(page, res)
        
        if (!page.data.isNet) {
          page.setData({
            isNet: true
          })
        }
      },
      // fail执行时当断网处理
      fail: function () {
        // console.log('fail111')
        // console.log(obj)
        wx.hideLoading()
        // 防止fail 有时会执行两次，影响渲染
        if (!isOne) {
          return
        }
        if (page.clearpop){
          page.clearpop()
          // page.setData({
          //   percent:0
          // })
        }
        page.setData({
          isNet: false,
          isRequested: false,
          overdue: true,  //请求超时弹框是否显示
        })
        
        // 记录本次请求，加载时，执行page实例的reloadFn即可
        page.reloadFn = wxRequest(obj, cb, page, 1)
        isOne = false
      }
    })
  }

  if (type) {
    page.isRequested = true
  }

  return type ? cachFn : cachFn()
}

module.exports.wxRequest = wxRequest