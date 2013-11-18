// When page ready
$(document).ready(function(){
  
  if($('#disclaimerWrapper').length != 0)
  {
    $.fancybox.open([
    {
      href : '#disclaimerWrapper',
      closeBtn:false,
      keys : {
        close  : null
      }
    }]);
  }
  
  var numberOfComments = json.modules.comments.comments.length;
  if(numberOfComments >1) $('#numberComment a').html(numberOfComments+" comments");
  else $('#numberComment a').html(numberOfComments+" comment");

  $('#shareDiv').share({
    networks: ['facebook','googleplus','twitter','linkedin']
  });
});

