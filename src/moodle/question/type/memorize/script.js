(function() {

  var Memorize = {
    wordItem: '.que.memorize table.generaltable .optiontext',
    wordItemCell: '.generaltable tr td:last-child',
    answerItem: '.que.memorize table.generaltable .option_answer',
    selectList: function(selector){
      return document.querySelectorAll(selector)
    },
    showList: function(list){
      list.forEach(function(item){
        item.setAttribute('style', 'display: block;')
      })
    },
    cleanList: function(list){
      list.forEach(function(item){
        item.innerText = ''
      })
    },
    blockList: function(list){
      list.forEach(function(el){
        var emptyFunction = function(){ return false }
        el.ondragstart = emptyFunction
        el.onselectstart = emptyFunction
        el.oncontextmenu = emptyFunction
      })
    },
    showAnswer: function(){
      this.cleanList(this.selectList(this.wordItem))
      this.showList(this.selectList(this.answerItem))
    },
    blockCells: function(){
      this.blockList(this.selectList(this.wordItemCell))
    },
    timerStart: function () {
      var clock = document.querySelector('#clock')
      var timer = 20
      var timerHandler = function(){
        if(timer > 0){
          clock.innerHTML = timer + ''
          timer--
        }else{
          clock.innerHTML = ''
          clearTimeout(timerId)
          Memorize.showAnswer()
        }
      }
      var timerId = setInterval(timerHandler, 1000)
    }
  }

  var previewPageId = 'page-question-preview'
  var reviewPageId = 'page-mod-quiz-review'
  var bodyElementId = document.querySelector('body').id

  if(bodyElementId === previewPageId){
    return
  }

  if(bodyElementId === reviewPageId){
    Memorize.showAnswer()
  }else{
    Memorize.blockCells()
    Memorize.timerStart()
  }

})()
