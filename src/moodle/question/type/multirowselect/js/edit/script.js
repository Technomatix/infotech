M.question_edit = {
  scoringMethod: null,
  methodData: null,
  rebuild: function () {
    var blockList = document.querySelectorAll('div.optionbox')
    var currentCnt = blockList.length
    var nextRowCnt = this.methodData[this.scoringMethod]
    var i = null

    if (currentCnt === 1 || currentCnt < nextRowCnt) {
      for (i = currentCnt; i < nextRowCnt; i++) {
        this.addQuestionRow(i + 1)
      }
    }
    if (currentCnt > nextRowCnt) {
      for (i = nextRowCnt; i < currentCnt; i++) {
        this.removeQuestionRow(blockList[i])
      }
    }
    document.querySelector('input[name=numberofrows]').value = nextRowCnt
  },
  template: function (number) {
    return '<br>' +
      '    <div class="optionbox">' +
      '    <div class="option_question"><div class="optionandresponses"><div class="optiontext"><div class="form-group row fitem">' +
      '        <div class="col-md-3">' +
      '            <span class="float-sm-right text-nowrap">' +
      '                <abbr class="initialism text-danger" title="Необходимо заполнить"><i class="icon fa fa-exclamation-circle text-danger fa-fw " title="Необходимо заполнить" aria-label="Необходимо заполнить"></i></abbr>' +
      '            </span>' +
      '            <label class="col-form-label d-inline " for="id_option_' + number + '">' +
      '                Вопрос ' + number +
      '            </label>' +
      '        </div>' +
      '        <div class="col-md-9 form-inline felement" data-fieldtype="textarea">' +
      '            <textarea name="option_' + number + '" id="id_option_' + number + '" class="form-control " wrap="virtual" rows="3" cols="50"></textarea>' +
      '            <div class="form-control-feedback invalid-feedback" id="id_error_option_' + number + '">' +
      '            - Вы должны ввести значение.' +
      '            </div>' +
      '        </div>' +
      '    </div>' +
      '</div></div></div></div>'
  },
  validateBehavior: function (element) {
    var parent = element.parentElement.parentElement
    if (element.value.length === 0) {
      !parent.classList.contains('has-danger') && parent.classList.add('has-danger')
      !element.classList.contains('is-invalid') && element.classList.add('is-invalid')
      element.setAttribute('aria-invalid', true)
    } else {
      parent.classList.contains('has-danger') && parent.classList.remove('has-danger')
      element.classList.contains('is-invalid') && element.classList.remove('is-invalid')
      element.setAttribute('aria-invalid', false)
    }
  },
  addQuestionRow: function (number) {
    var _this = this
    var blockList = document.querySelectorAll('div.optionbox')
    if (blockList.length) {
      blockList[blockList.length - 1].insertAdjacentHTML('afterend', this.template(number))

      blockList = document.querySelectorAll('div.optionbox')
      blockList[blockList.length - 1].querySelector('textarea').addEventListener('focusout', function (event) {
        _this.validateBehavior(event.target)
      })
    }
  },
  removeQuestionRow: function (element) {
    if (element.parentNode) {
      element.parentNode.removeChild(element)
    }
  },
  onSubmitForm: function () {
    var _this = this
    document.querySelectorAll('input[type=submit][name=updatebutton],input[type=submit][name=submitbutton]').forEach(function (element) {
      element.addEventListener('click', function (event) {
        var isValid = true
        document.querySelectorAll('div.optionbox textarea').forEach(function (element) {
          if (element.value.length === 0) {
            isValid = false
            _this.validateBehavior(element)
          }
        })
        !isValid && event.preventDefault()
      })
    })
  }
}
M.question_edit.init = function (YUI, options) {
  this.scoringMethod = options.scoringMethod
  this.methodData = JSON.parse(options.methodData)
  var _this = this

  var ready = function (callback) {
    // in case the document is already rendered
    if (document.readyState != 'loading') {
      callback()
    // modern browsers
    } else if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', callback)
    // IE <= 8
    } else {
      document.attachEvent('onreadystatechange', function () {
        if (document.readyState == 'complete') callback()
      })
    }
  }

  ready(function () {
    _this.rebuild()
    _this.onSubmitForm()

    var radioInputs = document.querySelectorAll('[name=scoringmethod]')
    for (var i = 0, max = radioInputs.length; i < max; i++) {
      radioInputs[i].onclick = function () {
        _this.scoringMethod = this.value

        _this.rebuild()
      }
    }
  })
}
