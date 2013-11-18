// When page ready
var selectIssue = false;

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
  
  // Create the root html element of each tree
  $.each(json.trees, function(key, tree)
    {
    $('#treeWrapper').append('<div class="TreeEntry"><img class="tooggleButton" src="'+json.global.webroot+'/privateModules/journal/public/images/arrow-bottom.png"/><h4>'+tree.title+' </h4><div id="categoryTree-'+tree.key+'" class="categoryTree"></div>');
    /* Init trees */
    $("div.categoryTree:last").dynatree({
      debugLevel : 0,
      checkbox: true,
      selectMode: 3,
      children: tree.children,
      onSelect: function(select, node) {
        searchDatabase();
      },
      onDblClick: function(node, event) {
        node.toggleExpand();
      },
     
        cookieId: "dynatreeEdit-"+key,
        idPrefix: "dynatreeEdit-"+key
      });
    }
  );  
    
  $('.issueButton').click(function(){
    var key = parseInt($(this).attr('key'));
    $('.issueSelected').each(function(){
      $(this).removeClass('issueSelected');
    })
    if(selectIssue == key) selectIssue = false;
    else
      {
      $(this).addClass('issueSelected');
      selectIssue = key;
      }
    searchDatabase();
  });
  if(json.selectedIssue != "")
    {
    selectIssue = parseInt(json.selectedIssue);
    $('.issueButton[key='+selectIssue+']').addClass('issueSelected');
    }

   
  // Init instant search
  $('#live_search').keyup(function(){
      searchDatabase();
    });
      
  // Init tree toogle
  $('img.tooggleButton').click(function(){
    var tree = $(this).parent('div.TreeEntry').find('div.categoryTree');
    if(tree.is(':visible'))
      {
      $(this).attr('src', json.global.webroot+'/privateModules/journal/public/images/arrow-right.png');
      }
    else
      {
      $(this).attr('src', json.global.webroot+'/privateModules/journal/public/images/arrow-bottom.png');
      }
    tree.toggle();
  });
  
  searchDatabase()
})

function getSelectedCategories()
  {
  var result = new Array();
  $("#treeWrapper div.categoryTree").each(function(){
    var nodes = $(this).dynatree("getSelectedNodes");
    $.each(nodes, function(index, value){
      result.push(value.data.key);
    });
  });
  return result;
  }

//** Query the api */
function searchDatabase()
  {
  var fullQuery = "text-journal.enable:true ";
  var query = $('#live_search').val();
  if(query.indexOf(":") != -1)
    {
    fullQuery += "AND "+query+" ";
    }
  else if(query != "" && query!="Search...")
    {
    fullQuery += "AND (name:"+query+" OR description:"+query+") ";
    }
    
  var categories = getSelectedCategories();
  if(categories.length != 0)
    {
    fullQuery+= " AND (";
    $.each(categories, function(index, value){
      if(index != 0) fullQuery += " OR ";
      fullQuery+= " text-journal.categories:"+value+" ";
    });
    fullQuery+= ")";
    }
    
  if(selectIssue)
    {
    fullQuery+= " AND (";
    fullQuery+= " text-journal.issue:"+selectIssue+" ";
    fullQuery+= ")";
    }
  if(json.selectedCommunity != "")
    {
    fullQuery+= " AND (";
    fullQuery+= " text-journal.community:"+json.selectedCommunity+" ";
    fullQuery+= ")";
    }
    
  $('img#searchLoadingImg').show();
  ajaxWebApi.ajax({
        method: 'midas.journal.search',
        args: "query="+fullQuery,
        success: function (retVal) {
          $('img#searchLoadingImg').hide();
          var total = 0;
          $('.SearchResults').html("");
          $.each(retVal.data, function(index, value)
          {
          total = value.total;
          addAndFormatResult($('.SearchResults'), {'rating': value.rating, 'type': value.type,
            'id':value.revisionId, 'title': value.title, "logo": value.logo,
            'description': value.description, 'statistics': value.statistics,
            'authors': value.authors})

          })
               
          if(total > 1)
            {
            $('.SearchCount').html(total+ " resources available.")  
            }
          else
            {
            $('.SearchCount').html(total+ " resource available.") 
            }
        },
        error: function (retVal) {
            midas.createNotice(retVal.message, 3000, 'error');
        },
        complete: function () {
        }
    });
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
    newElement.find('.ResultLogo').remove();
    }
  newElement.find('.ResultDescription').dotdotdot();
  return str; 
};