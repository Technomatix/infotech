M.question_edit = {
  scoringMethod: null,
  scoringSubMethod2: function (item) {
    !item.classList.contains('scoringsub2') && item.classList.add('scoringsub2')
  },
  scoringSubMethod3: function (item) {
    item.classList.contains('scoringsub2') && item.classList.remove('scoringsub2')
  },
  run: function (method) {
    document.querySelectorAll('.optiontext > div').forEach(method)
  }
}
M.question_edit.init = function (YUI, options) {
  this.scoringMethod = options.scoringMethod
  var _this = this

  var ready = function (callback) {
    // in case the document is already rendered
    if (document.readyState != 'loading') callback()
    // modern browsers
    else if (document.addEventListener) document.addEventListener('DOMContentLoaded', callback)
    // IE <= 8
    else document.attachEvent('onreadystatechange', function () {
        if (document.readyState == 'complete') callback()
      })
  }

  ready(function () {
    if (_this.scoringMethod === 'scoringsub2') {
      _this.run(_this.scoringSubMethod2)
    }

    var radioInputs = document.querySelectorAll('[name=scoringmethod]')
    for (var i = 0, max = radioInputs.length; i < max; i++) {
      radioInputs[i].onclick = function () {
        switch (this.value) {
          case 'scoringsub2':
            _this.run(_this.scoringSubMethod2)
            break
          case 'scoringsub3':
            _this.run(_this.scoringSubMethod3)
            break
        }
      }
    }
  })
}
