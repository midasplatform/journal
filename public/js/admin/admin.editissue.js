// When page ready
$(document).ready(function(){  
  $('.datepicker').pickadate({
    clear: 'Clear selection',
    format: 'yyyy-mm-dd',
    formatSubmit: 'yyyy-mm-dd'
  })
  
  $('form').submit(function(){
    $("input").attr('disabled', false);
  })
});

function ChangeAuthorLicense(value)
  { 
  $('#authorLicense').val(value);
  }
  
function ChangeReaderLicense(value)
  {
  $('#readerLicense').val(value);
  }  