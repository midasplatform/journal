var isFinalReview= false;
$(document).ready(function(){
  isFinalReview = parseInt(json.listArray.list.type) === 2;
  processQuestionUpdate(true);
  $("div#questionWrapper_"+$("select#listTopics").val()).show();
  $("select#listTopics").change(function(){
    $('div.questionWrapper').hide();
    $("div#questionWrapper_"+$("select#listTopics").val()).show();
  })

  $('.selectTopic').click(function(){
    $("select#listTopics").val($(this).attr('value'))
    $("select#listTopics").trigger('change');
  })

  if((document.createElement('progress').max === undefined))
    {
    $('progress').parents('tr#summaryProgress').hide();
    }

  $('.selectMultipleWrapper select').change(function(){
    var question = $(this).parents('.questionElement').attr('key');
    var topic = $("select#listTopics").val();
    json.listArray.topics[topic].questions[question].value = $(this).val();
    processQuestionUpdate();
  });
  $('.questionElement textarea').change(function(){
    var question = $(this).parents('.questionElement').attr('key');
    var topic = $("select#listTopics").val();
    json.listArray.topics[topic].questions[question].commentValue = $(this).val();
    processQuestionUpdate();
  });

  $('#globalComment').change(function(){
    json.listArray.list.comment = $(this).val();
  });
  $('#certificationLevel').change(function(){
    json.listArray.list.certificationLevel = $(this).val();
  });

  $('#saveReview').click(function(){
    var url = json.global.webroot+"/reviewosehra/submit?revision_id="+json.listArray.revision_id;
    if(json.listArray.review_id != "")
      {
      url = json.global.webroot+"/reviewosehra/submit?review_id="+json.listArray.review_id;
      }
    $.post(url, {is_complete: percentage, cache_summary: $("#summaryTable").html(), content: JSON.stringify(json.listArray)},function(data)
    {
      window.location.href = data;
    }, 'json')
  });
  $('#certificationWrapper').hide();
  populateCertificationMatrixTable();
  $('#certificationWrapper').show();
});

function populateCertificationMatrixTable(){
  var certiArray = [
    ['Level 1',
     'Pass', 'Pass', 'Apache 2', 'None',
     'Large # Non-critical Issues', 'Large # Non-critical Issues',
     'Existing Tests Pass', 'Large # Non-critical Issues'
    ],
    ['Level 2',
     'Pass', 'Pass', 'Apache 2', 'Basic',
     'Small # Non-critical Issues', 'Small # Non-critical Issues',
     'Existing + Some R. Tests', 'Small # Non-critical Issues'
    ],
    ['Level 3',
     'Pass', 'Pass', 'Apache 2', 'Substantial',
     'No Issues', 'No Issues',
     'Existing + >= 50% Coverage', 'No Issues'
    ],
    ['Level 4',
     'Pass', 'Pass', 'Apache 2', 'All Required',
     'No Issues', 'No Issues',
     'Existing + >= 90% Coverage', 'No Issues'
    ]
  ];

  var certiMatrixHeader = [
    "Name/Number Space",
    "Dependency/SAC",
    "Open Source License",
    "Documentation",
    "Code Review",
    "Test Installation",
    "Regression Testing",
    "Functional Testing"
  ];
  // generate the certification table head hard-coded javascript array
  var html = "<tr><th></th>";
  $.each(certiMatrixHeader, function(i, v){
    // certification topics
    html += "<th>" + v + "</th>";
  });
  html += "</tr>";
  $('table#certificationTable thead').append(html);
  for (i = 0; i < certiArray.length; ++i) {
    html="";
    if(i%2 == 0) html += "<tr class='topicSum'>";
    else html += "<tr class='even' class='topicSum'>";
    for (j = 0; j < certiArray[i].length; ++j) {
      if (j === 0){
        html += "<td id='levelInfo'>" + certiArray[i][j] + "</td>";
      }
      else{
        html += "<td>" + certiArray[i][j] + "</td>";
      }
    }
    html += "</tr>";
    $('#certificationWrapper table#certificationTable tbody').append(html);
  }
}
/** Simple templating mechanism */
function questionTemplate(id, html, values) {
  html = "<div class='questionElement' key='"+id+"' id='questionElement_"+id+"'>"+html+"</div>";
  html = html.replace("{description}", values.description);
  if(values.comment == 0)
    {
    html = html.replace('class="commentWrapper"', 'class="commentWrapper" style="display:none"');
    }
  if(values.attachfile == 0)
    {
    html = html.replace('class="uploadWrapper"', 'class="uploadWrapper" style="display:none"');
    }
  return html;
};

var percentage;
function processQuestionUpdate(init){
  if(init)
    {
    var template = $('div#templateQuestion').html();
    $('#descriptionQuestionList').html(json.listArray.list.description);
    $('#globalComment').val(json.listArray.list.comment);
    $('#certificationLevel').val(json.listArray.list.certificationLevel);
    $.each(json.listArray.topics, function(i, v){
      // Summary
      var html = "";
      if(i%2 == 0)html += "<tr class='topicSum' id='topicSum_"+i+"'>";
      else html += "<tr class='even' class='topicSum' id='topicSum_"+i+"'>";
      html += "<td><a class='selectTopic' value='"+i+"'>"+v.name+"</a></td>";
      if (isFinalReview){
        html += "<td><input type='text' size='1' id='questionLevel_"+i+"' disabled='disabled'/></td>";
        html += "<td>"
        Object.keys(json.listArray.topics[i].questions).forEach(function(questionID) {
          if(typeof json.listArray.topics[i].questions[questionID].commentValue !== 'undefined') {
            html += "<textarea disabled=true id='questionComment_"+i+"'>"+json.listArray.topics[i].questions[questionID].commentValue.replace(/</g,"&lt;").replace(/>/g,"&gt;")+"'</textarea>";
          }
        });
        html+="</td>";
      }
      else{
        html += "<td><input type='checkbox' disabled='disabled' id='topicComple_"+i+"'/></td>";
      }
      html += "</tr>";
      $('table#summaryTable tbody').append(html);

      // Questions
      $("select#listTopics").append("<option value='"+i+"'>"+v.name+"</options>");
      $("div#divRightPanel h4").after("<div class='questionWrapper' id='questionWrapper_"+i+"'>");
      $.each(v.questions, function(j, q){
        $("div#questionWrapper_"+i).append(questionTemplate(j, template, q));
      });
    });
    }

  // Set the questions content
  percentage = 0;
  var totalQuestion = 0;
  var totalQuestionAnswered = 0;
  $.each(json.listArray.topics, function(i, v){
    var isComplete = v.questions.length != 0;
    var levelValue = 0;
    $.each(v.questions, function(j, q){
        if (isFinalReview) levelValue = q.value;
        totalQuestion++;
        if(q.value == 0) isComplete = false;
        if(q.value != 0) totalQuestionAnswered++;
        $('#questionElement_'+j+" select").val(parseInt(q.value));
        $('#questionElement_'+j+" textarea").val(q.commentValue);
        $('#questionElement_'+j+" .downloadButton .fileNameUploaded").click(function(){
          var item_id = $('#questionElement_'+j+" .fileItemId").val();
          window.location.href = json.global.webroot+"/download/?items="+item_id;
        })
        if(q.attachfileValue == "")
          {
          $('#questionElement_'+j+" .downloadButton").hide();
          $('#questionElement_'+j+" .fileItemId").val(q.attachfileValue);
          }
        else
          {
          $('#questionElement_'+j+" .uploadButton .inputUpload").html("Attach a different file...");
          $('#questionElement_'+j+" .downloadButton .fileNameUploaded").html(q.attachfileValue[0]);
          $('#questionElement_'+j+" .fileItemId").val(q.attachfileValue[1]);
          }
        var revision_id = json.listArray.revision_id;
        var uploadElement = $('#questionElement_'+j+" .uploadButton .fileupload");
        uploadElement.fileupload({
              dataType: 'json',
              url:  json.global.webroot+'/reviewosehra/submit/upload?revision_id='+revision_id,
              start:function(){
                $('#questionElement_'+j+" .uploadButton").hide();
                $('#questionElement_'+j+" .uploadProgress").show();
                $('#questionElement_'+j+" .uploadProgress .progressValue").html("");
              },
              done: function (e, data) {
                $('#questionElement_'+j+" .uploadButton").show();
                $('#questionElement_'+j+" .uploadButton .inputUpload").html("Attach a different file...");
                $('#questionElement_'+j+" .uploadProgress").hide();
                if(data.result[0]['size'] > 0);
                  {
                  var item_id = data.result[0]['item_id'];
                  $('#questionElement_'+j+" .downloadButton").show();
                  $('#questionElement_'+j+" .downloadButton .fileNameUploaded").html(data.result[0]['name']);
                  $('#questionElement_'+j+" .fileItemId").val(item_id);
                  var question = $('#questionElement_'+j).attr('key');
                  var topic = $("select#listTopics").val();
                  json.listArray.topics[topic].questions[question].attachfileValue = [data.result[0]['name'], item_id];
                  processQuestionUpdate();
                  }
              }
          });

        uploadElement.bind('fileuploadprogress', function (e, data)
          {
          var progress = parseInt(data.loaded / data.total * 100, 10);
          $('#questionElement_'+j+" .uploadProgress .progressValue").html("("+progress+"%)");
          });
      });
    if (isFinalReview){
      $('#questionLevel_'+i).attr('value', levelValue);
      Object.keys(json.listArray.topics[i].questions).forEach(function(questionID) {
        if(typeof json.listArray.topics[i].questions[questionID].commentValue !== 'undefined') {
          $('#questionComment_'+i).html(json.listArray.topics[i].questions[questionID].commentValue.replace(/</g,"&lt;").replace(/>/g,"&gt;"));
        }
      });
    }
    else
    {
      $('#topicComple_'+i).attr('checked', isComplete);
      $('#topicComple_'+i).prop('checked', isComplete);
    }
    });
  percentage = parseInt(100 * totalQuestionAnswered/totalQuestion);
  $('progress').attr("value", percentage);
  }
