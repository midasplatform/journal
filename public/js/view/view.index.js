// When page ready
$(document).ready(function(){
  
  $('.linkToArchive').click(function(){   
    return confirm("You are about to be redirected to an archive version. The content may not be accurate.");
  })
  
  $("#revisionSelector").change(function(){
    window.location.href = json.global.webroot+"/journal/view/"+$(this).val();
  })
  
  var numberOfComments = json.modules.comments.comments.length;
  if(numberOfComments >1) $('#numberComment a').html(numberOfComments+" comments");
  else $('#numberComment a').html(numberOfComments+" comment");

  $('#shareDiv').share({
    networks: ['facebook','googleplus','twitter','linkedin']
  });
});

