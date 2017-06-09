// When page ready
var selectIssue = false;
var resizeEvent;
var lastIndex=0;

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

  $('.issuePage').fancybox({type: 'ajax'});

  function selectCategory (select, node) {
        $('#infoElement').hide();
        searchDatabase(false);
  }
  // Create the root html element of each tree
  $.each(json.trees, function(key, tree)
    {
    $('#treeWrapper').append('<div class="TreeEntry"><img class="tooggleButton" src="'+json.global.webroot+'/privateModules/journal/public/images/arrow-bottom.png"/><h4>'+tree.title+' </h4><div id="categoryTree-'+tree.key+'" class="categoryTree"></div>');
    /* Init trees */
    $("div.categoryTree:last").dynatree({
      debugLevel : 0,
      checkbox: true,
      selectMode: 3,
      title: tree.title,
      children: tree.children,
      onSelect: selectCategory,
      onDblClick: function(node, event) {
        node.toggleExpand();
      },

        cookieId: "dynatreeEdit-"+key,
        idPrefix: "dynatreeEdit-"+key
      });

    if(tree.children.length > 7)
      {
      $("div.categoryTree:last").hide();
      $("div.categoryTree:last").parent().find('img.tooggleButton').attr('src', json.global.webroot+'/privateModules/journal/public/images/arrow-right.png');
      }
    }
  );
  var cookieVal= '';
  var name= "searchParams=";
  // Find searchParams cookie and capture values if it exists
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            cookieVal = c.substring(name.length, c.indexOf("}")+1);
        }
    }

  //Set the search boxes from the search query if the cookie exists
  if(cookieVal !=name && cookieVal != '') {
    cval = JSON.parse(cookieVal);
    $("#treeWrapper div.categoryTree").each(function(i, n){
      var treeObject = $(this).dynatree("getTree");
      treeObject.options.onSelect=null;
      Object.getOwnPropertyNames(cval).forEach(function(item,index) {
          cval[item].forEach(function( key, index) {
            treeObject.selectKey(key);
          });
      });
      treeObject.options.onSelect=selectCategory;
    });
    var queryString='';
    cval.query.forEach(function (entry, index) {
      queryString += entry + " ";
    })
    //$('#live_search').attr("value",queryString);
  };

  $('.issueButton .issueTitle, .issueButton .issueSubTitle').click(function(){
    var container = $(this).parents('.issueButton');
    var key = parseInt(container.attr('key'));
    $('.issueSelected').each(function(){
      $(this).removeClass('issueSelected');
      $(this).find('.issueDetails').hide();
    })
    if(selectIssue == key || key === -999) selectIssue = false;
    else
      {
      container.addClass('issueSelected');
      container.find('.issueDetails').show();
      selectIssue = key;
      }
    $('#infoElement').hide();
    searchDatabase(false);
  });
  if(json.selectedIssue != "")
    {
    selectIssue = parseInt(json.selectedIssue);
    $('.issueButton[key='+selectIssue+']').addClass('issueSelected');
    }


  // Init instant search
  $('#search_button').click(function(){
      $('#infoElement').hide();
      searchDatabase(false);
    });

  $('#clear_button').click(function(){
      $('#infoElement').hide();
      $('#live_search').val("Search...");
      $('#live_search_value').val("init");
      document.cookie = 'pastSearch=;expires= Thu, 01 Jan 1970 00:00:01 GMT; path='+$(".webroot").attr("value");
      $("#treeWrapper div.categoryTree").each(function(i, n){
         $(this).dynatree("getRoot").visit(function(node) {
           node.select(false);
         })
      })
      searchDatabase(false);
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

  searchDatabase(false)
})

function getSelectedCategories()
  {
  var result = new Array();
  $("#treeWrapper div.categoryTree").each(function(i, n){
    var selected =  new Array();
    var nodes = $(this).dynatree("getSelectedNodes");
    $.each(nodes, function(index, value){
      selected.push(value.data.key);
    });
    if (selected.length > 0) result.push(selected);
  });
  return result;
  }

//** Query the api */
function searchDatabase(append)
  {
  // Values for capturing the search parameters
  var selections = {};
  selections["Category"] = [];
  selections["License"] = [];
  selections["Certified"] = [];
  selections["Code"] = [];
  selections["query"] = [];
  selections['OSEHRA VistA'] = [];

  var fullQuery = "text-journal.enable:true ";

  var query = $('#live_search').val();
  if(query != "" && query!="Search...")
    {
    var vals = [];

    // Find all the "" pairs
    var re = /".*"/i;
    while (true)
      {
      var val = query.match(re);
      if (val == null)
        {
        break;
        }
      vals = vals.concat(val);
      query = query.replace(val, '');
      }

    vals = vals.concat(query.split(" "));

    fullQuery += "AND ("
    // Account for the "Institution" and "Author" links from the submission page
    // Replace the string with the correct SOLR field
    // Assumes that only one of the values will be searched for at a time for now
    var re = /(.*)\:(.*)/i;
    var val = re.exec(query);
    if (val != null)
      {
      switch(val[1])
        {
        case "institution":
          query = query.replace(val[1], 'text-journal.insitution');
          query = query.replace(":"+val[2], ":("+val[2]+")");
          fullQuery += query +" OR ";
          break;
        case "authors":
          query = query.replace(val[1], 'text-journal.authors');
          query = query.replace(":"+val[2], ":("+val[2]+")");
          fullQuery += query +" OR ";
          break
        case "tags":
          query = query.replace(val[1], 'text-journal.tags');
          query = query.replace(":"+val[2], ":("+val[2]+")");
          fullQuery += query +" OR ";
          break
        case "submitter":
          query = query.replace(val[1], 'text-journal.submitter');
          query = query.replace(":"+val[2], ":("+val[2]+")");
          fullQuery += query +" OR ";
          break
        default:
          fullQuery += query +" OR ";
          break
        }
      }

    // Remove any empty values
    vals = vals.filter(function(val){
      return val !== "";
    });

    // Re-construct query
    if( vals.length > 0 )
      {
      query = vals[0];
      selections['query'].push(vals[0]);
      for (i = 1; i < vals.length; i++)
        {
        selections['query'].push(vals[i]);
        query += " AND ";
        query += vals[i];
        }
      fullQuery += "name:("+query+") OR description:("+query+") OR ngram_search:("+query+")";
      }
    fullQuery += ")"
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
  var allQuery = '';
  var categories = getSelectedCategories();
  var certLevel =  [];
  document.cookie = "pastSearch=; expires= Thu, 01 Jan 1970 00:00:01 GMT; path="+$(".webroot").attr("value");
  if(categories.length != 0)
    {
    document.cookie = "searchParams=; expires= Thu, 01 Jan 1970 00:00:01 GMT; path="+$(".webroot").attr("value");
    $.each(categories, function(idx, val){

      fullQuery+= " AND (";
      $.each(val, function(index, value){
        if(index != 0) fullQuery += " OR ";
        if(value.indexOf("certified") != -1)
          {
          fullQuery+= "text-journal.certification_level:"+value.charAt(value.length - 1)+" ";
          certLevel.push(value.charAt(value.length - 1));
          selections["Certified"].push(value)
          }
        else if(value.indexOf("submission_type") != -1)
          {
          fullQuery+= "text-journal.submission_type:"+value.charAt(value.length - 1)+" ";
          selections["OSEHRA VistA"].push(value)
          }
        else if(value.indexOf("code_in_flight") != -1)
          {
          fullQuery+= "name:\"Code in Flight\" ";
          selections["Code"].push( value);
          }
        else if(value.indexOf("with_code") != -1)
          {
          fullQuery+= "text-journal.has_code:true ";
          selections["Code"].push( value);
          }
        else if(value.indexOf("with_test_code") != -1)
          {
          fullQuery+= "text-journal.has_test_code:true ";
          selections["Code"].push( value);
          }
        else if(value.indexOf("with_review") != -1)
          {
          fullQuery+= "text-journal.has_reviews:true ";
          selections["Certified"].push( value)
          }
        else if (value.indexOf("license") != -1)
          {
          fullQuery+= "text-journal.source_license:"+value.charAt(value.length - 1)+" ";
          selections["License"].push(value)
          }
        else
          {
          fullQuery+= " text-journal.categories:"+value+" ";
          selections["Category"].push(value)
          }
      });
      fullQuery+= ")";
    });

    }
  var mydate = new Date();
  mydate.setMinutes(mydate.getMinutes()+30);
  document.cookie = 'searchParams='+ JSON.stringify(selections) +'; expires= '+ mydate.toUTCString()+ '; path='+$(".webroot").attr("value");
  allQuery= fullQuery.replace(/AND \(text-journal.cer[(text\-journal\.certification\_level:1-4OR ]+ \)/,"")
  $('img#searchLoadingImg').show();
  ajaxSearch(append,fullQuery,allQuery,certLevel);
}


function ajaxSearch(append,fullQuery,allQuery,certLevel) {
  var limit = 25;
  var shown = 0;
  var foundIDs = new Array();

  if(!append) {
    shown = 0;
    lastIndex = 1;
  }
  else {
    lastIndex = lastIndex + limit;
  }
  ajaxWebApi.ajax({
        method: 'midas.journal.search',
        args: "offset="+lastIndex-1+"&query="+fullQuery+"&level="+certLevel+"&secondQuery="+allQuery,
        log: true,
        success: function (retVal) {
          $('img#searchLoadingImg').hide();
          if(!append) $('.SearchResults').html("");
          if(!append) $('#noResultElement').show();

          $.each(retVal.data[0].slice(lastIndex), function(index, value)
          {
          shown = $('.resourceLink').length;
          if(shown >= (limit + lastIndex)) return false;
          $('#noResultElement').hide();
          addAndFormatResult($('.SearchResults'), {'rating': value.rating, 'type': value.type,
            'id':value.revisionId, 'title': value.title, "logo": value.logo,
            'description': value.description, 'statistics': value.statistics,
            'authors': value.authors, 'isCertified' : value.isCertified, 'certifiedLevel': value.certifiedLevel,
            'pastCertificationRevisionNum': value.pastCertificationRevisionNum,
            'pastCertificationRevisionKey': value.pastCertificationRevisionKey,
            'license': value.license}, foundIDs)
          })
          var mydate = new Date();

          mydate.setMinutes(mydate.getMinutes()+30);
          document.cookie = 'pastSearch='+ JSON.stringify( retVal.data[1]) +'; expires= '+ mydate.toUTCString()+ '; path='+$(".webroot").attr("value");
          if(shown == (limit + lastIndex))
            {
            $('#showMoreResults').show();
            $('#showMoreResults a').unbind('click').click(function(){
              searchDatabase(true);
            })
            }
          else
            {
            $('#showMoreResults').hide();
            }

          if(shown == 0)
            {
            $('.SearchCount').hide();
            }
          else if(shown > 1)
            {
            $('.SearchCount').show();
            $('.SearchCount').html(shown + " resources available.")
            }
          else
            {
            $('.SearchCount').show();
            $('.SearchCount').html(shown + " resource available.")
            }

          resizeEvent();
          setTimeout(resizeEvent, 200);
        },
        error: function (retVal) {
            midas.createNotice(retVal.message, 3000, 'error');
        },
        complete: function () {
        }
    });
}

/** Simple templating mechanism */
function addAndFormatResult(container, values, foundIDs) {
  var id = values['id'];
  if (foundIDs.indexOf(id) === -1) {
    foundIDs.push(id);
    var str = document.getElementById('SearchResultTemplate').innerHTML;
      if(values.pastCertificationRevisionKey !== "")
        {
        values.id = values.pastCertificationRevisionKey;
        }
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

    if(values.isCertified == 0)
      {
      newElement.find('.CertifiedWrapper').remove();
      newElement.find('.CertifiedLevel').remove();
      }
    else
      {
      newElement.find('.CertifiedLevel').html("(Level "+values.certifiedLevel+")");
      }

    newElement.find('.ResultDescription').dotdotdot();
    return str;
  }
};
