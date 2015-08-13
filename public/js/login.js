// When page ready
$(document).ready(function(){
  $( "#toplogin" ).appendTo("#loginWrapper");
  $( "#toplogin" ).css('float', "initial");
  $( "#toplogin" ).css('color', "black");
  $( "#toplogin a" ).css('color', "black");
  $( "#toplogin div" ).css('position', "static");
  $( "#toplogin div" ).css('margin-top', "10px");
  $( "#toplogin div a" ).css('position', "static");
  $('#loginWrapper').css({
    color:"black",
    border:"1px solid #ddd",
    padding:"10",
    position:"relative",
    width:"500px",
    margin:"auto"    
  });
});