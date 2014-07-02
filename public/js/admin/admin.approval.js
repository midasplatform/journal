// When page ready
var selectIssue = false;
var resizeEvent;

$(document).ready(function(){  
  $('.issueTitle').each(function(){
    $(this).dotdotdot({
    callback	: function( isTruncated, orgContent ) {
      if(isTruncated)
        {
        $(this).qtip({
            content: {
                attr: 'qtipconditionnal'
            }
        });
        }
    }}
    );
 });  

  
  resizeEvent = function(){
    $.each($('.SearchResultEntry'),function(){
      $(this).find('.ResultTitle').dotdotdot( {'height': 20});
      $(this).find('.ResultDescription').trigger("update");
    })

  }
  
  $(window).resize(resizeEvent);

  listArticles(json.articles)
})


//** Query the api */
function listArticles(aritcles)
  {
  var total = 0;
  $.each(aritcles, function(index, value)
    {
    total = value.total;
    $('#noResultElement').hide();
    addAndFormatResult($('.SearchResults'), {'type': value.type,
      'id':value.revisionId, 'title': value.title, "logo": value.logo,
      'description': value.description, 'statistics': value.statistics,
      'authors': value.authors})
    })
  resizeEvent();
  setTimeout(resizeEvent, 200);
  }


/** Simple templating mechanism */
function addAndFormatResult(container, values) {
  var str = document.getElementById('SearchResultTemplate').innerHTML;
  $.each(values, function(key,value)
   {
   str = str.replace("{"+key+"}", value);
   str = str.replace("{"+key+"}", value);
   str = str.replace("{"+key+"}", value);
   }
  );
  container.append(str);
  var newElement = $('div.SearchResultEntry:last');
  newElement.find('.ResultTitle').dotdotdot( {'height': 20});
            
  if(values.logo == "")
    {
    newElement.find('.ResultLogo:first').remove();
    }
  else
    {
    newElement.find('.ResultLogo:last').remove();
    }
  
    
  newElement.find('.ResultDescription').dotdotdot();
  return str; 
};
