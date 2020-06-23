var getParentByClass = function (elem, className) {
  for ( ; elem && elem !== document; elem = elem.parentNode ) {
    if ( elem.classList.contains(className) ) return elem;
  }
  return null;
};

document.querySelectorAll('.row-table .lastcol input').forEach(function(elem){
  elem.addEventListener("input", function() {
    var sumValue = 11;
    var blockTable = getParentByClass(this, 'row-table');
    if(blockTable !== null){
      var inputs = blockTable.querySelectorAll('.lastcol input');
      if(inputs.length) {
        inputs.forEach(function(el){
          var value = el.value > -1 ? el.value * 1 : 0;
          sumValue -= value;
        });

        if (sumValue > -1) {
          inputs.forEach(function(el){
            var value = el.value > -1 ? el.value * 1 : 0;
            el.setAttribute('max', (value + sumValue) + '');
          });
        } else {
          console.warn('Error: Sum block values must be up to 11.');
        }
      }
    }
  });
});

var eventInput = new Event('input');
document.querySelectorAll('.row-table').forEach(function(el){
  el.querySelector('.lastcol input').dispatchEvent(eventInput);
});
