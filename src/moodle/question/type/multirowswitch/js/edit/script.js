M.question_edit = {
  scoringMethod: null,
  defaultMethodData: null,
  rebuild: function () {
    var methodName = document.querySelector('input[type=radio][name=scoringmethod]:checked').value
    var methodDataRows = this.defaultMethodData[methodName]
    var weightButtons = {
      '1': document.querySelector('input[type=text][name=responsetext_1]').value,
      '2': document.querySelector('input[type=text][name=responsetext_2]').value
    }
    document.querySelector('#question-rows-block').innerHTML = ''

    for (var i = 0; i < methodDataRows.length; i++) {
      this.addQuestionRow((i + 1), methodDataRows[i], weightButtons)
    }

    document.querySelector('input[name=numberofrows]').value = methodDataRows.length
  },
  template: function (number, rowObject, weightButtons) {
    var checked1 = rowObject.checked * 1 === 1 ? ' checked="checked"' : ''
    var checked2 = rowObject.checked * 1 === 2 ? ' checked="checked"' : ''
    return '<hr>' +
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
      '            <textarea name="option_' + number + '" id="id_option_' + number + '" class="form-control " wrap="virtual" rows="3" cols="50">' + rowObject.text + '</textarea>' +
      '            <div class="form-control-feedback invalid-feedback" id="id_error_option_' + number + '">' +
      '            - Вы должны ввести значение.' +
      '            </div>' +
      '        </div>' +
      '    </div>' +
      '    <div class="option_answer"><div class="responses"><div class="form-group row  fitem femptylabel  " data-groupname="weightsarray_' + number + '">' +
      '        <div class="col-md-3">' +
      '            <span class="float-sm-right text-nowrap"></span>' +
      '            <label class="col-form-label d-inline " for="fgroup_id_weightsarray_' + number + '"></label>' +
      '        </div>' +
      '        <div class="col-md-9 form-inline felement" data-fieldtype="group">' +
      '            <label class="form-check-inline form-check-label fitem">' +
      '                <input type="radio" class="form-check-input" name="weightbutton_' + number + '" id="id_weightbutton_' + number + '_1" value="1"' + checked1 + '>' + weightButtons[1] +
      '            </label>' +
      '            <span class="form-control-feedback invalid-feedback" id="id_error_weightbutton_' + number + '"></span>' +
      '            <label class="form-check-inline form-check-label fitem">' +
      '                <input type="radio" class="form-check-input" name="weightbutton_' + number + '" id="id_weightbutton_' + number + '_2" value="2"' + checked2 + '>' + weightButtons[2] +
      '            </label>' +
      '            <span class="form-control-feedback invalid-feedback" id="id_error_weightbutton_' + number + '"></span>' +
      '            <div class="form-control-feedback invalid-feedback" id="id_error_weightsarray_' + number + '"></div>' +
      '        </div>' +
      '    </div></div></div>' +
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
  addQuestionRow: function (number, rowObject, weightButtons) {
    var _this = this
    var blockRows = document.querySelector('#question-rows-block')
    var blockList = blockRows.querySelectorAll('div.optionbox')
    if (blockList.length) {
      blockList[blockList.length - 1].insertAdjacentHTML('afterend', this.template(number, rowObject, weightButtons))
    } else {
      blockRows.innerHTML = this.template(number, rowObject, weightButtons)
    }

    blockList = document.querySelectorAll('div.optionbox')
    blockList[blockList.length - 1].querySelector('textarea').addEventListener('focusout', function (event) {
      _this.validateBehavior(event.target)
    })
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
  this.defaultMethodData = JSON.parse(options.defaultMethodData)
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
