// When page ready
$(document).ready(function(){
  
   $('input').iCheck({
    checkboxClass: 'icheckbox_minimal',
    radioClass: 'iradio_minimal',
    increaseArea: '20%' // optional
  });
  
  $('.datepicker').pickadate({
    clear: 'Clear selection',
    format: 'yyyy-mm-dd',
    formatSubmit: 'yyyy-mm-dd'
  })
});