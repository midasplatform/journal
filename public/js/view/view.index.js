// When page ready
$(document).ready(function(){
  
  $('.linkToArchive').click(function(){
    return confirm("You are about to be redirected to an archive version. The content may not be accurate.");
  })

  var numberOfComments = json.modules.comments.comments.length;
  if(numberOfComments >1) $('#numberComment a').html(numberOfComments+" comments");
  else $('#numberComment a').html(numberOfComments+" comment");

  $('#shareDiv').share({
    networks: ['facebook','googleplus','twitter','linkedin']
  });

  var table = document.getElementById('revisionTable');
  if (table) {
    var selected = table.getElementsByClassName('selected');
  }
  $(".clickable-row").click(function() {
    if (selected[0]) selected[0].className = 'clickable-row';
    this.className = 'selected';
    window.location.href = $(this).data("href");
  });
});
