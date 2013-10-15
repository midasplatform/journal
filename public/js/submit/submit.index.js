$(document).ready(function(){
  var template = $('div#templateQuestion').html();
  $.each(json.listArray.topics, function(i, v){
    $("select#listTopics").append("<option value='"+i+"'>"+v.name+"</options>");
    $("div#divRightPanel h4").after("<div class='questionWrapper' id='questionWrapper_"+i+"'>");
    $.each(v.questions, function(j, q){
    $("div#questionWrapper_"+i).append(questionTemplate(template, q));
    });
  });
  
  $("div#questionWrapper_"+$("select#listTopics").val()).show();
  $("select#listTopics").change(function(){
    $('div.questionWrapper').hide();
    $("div#questionWrapper_"+$("select#listTopics").val()).show();
  })
});

/** Simple templating mechanism */
function questionTemplate(html, values) {
  html = "<div class='questionElement'>"+html+"</div>";
  html = html.replace("{description}", values.description);
  return html; 
};