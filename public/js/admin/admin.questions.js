$(document).ready(function(){
  // Select question list
  $('#selectList').change(function(){
    window.location.href = json.global.webroot+"/reviewosehra/admin/questions?listId="+$(this).val();
  })  
 
  
  $('#newQuestionListLink').click(function(){
    initQuestionListForm(true);
  });
  $('#editListLink').click(function(){
    initQuestionListForm(false);
  });
  $('#newTopicLink').click(function(){
    initTopicForm(true);
  });
  $('#editTopicLink').click(function(){
    initTopicForm(false);
  });
  
  $('#newQuestionLink').click(function(){
    initQuestionForm(true);
  });
  
  // Forms ------
  
  // New/Edit Question list Form and popup
  var initQuestionListForm = function(newList)
    {
    $('#newQuestionList').dialog();
    if(newList)
      {
      $("#newQuestionList input[name=oldlist]").val("");
      $("#newQuestionList input[name=newname]").val("");
      $("#newQuestionList textarea[name=newdescription]").val("");
      $("#newQuestionList select:first").val("-1");
      $("#newQuestionList h3:first").show();
      $("#newQuestionList h3:last").hide();
      $("#deleteList").hide();
      }
    else
      {
      $("#newQuestionList input[name=oldlist]").val(json.list.list.questionlist_id);
      $("#newQuestionList input[name=newname]").val(json.list.list.name);
      $("#newQuestionList textarea[name=newdescription]").val(json.list.list.description);
      $("#newQuestionList select:first").val(json.list.list.category_id);
      $("#newQuestionList select:last").val(json.list.list.type);
      $("#newQuestionList h3:first").hide();
      $("#newQuestionList h3:last").show();
      $("#deleteList").show().unbind("click").click(function(){
        if(confirm("Do you want to delete the list?"))
          {
          window.location.href = json.global.webroot+"/reviewosehra/admin/questions?deleteListId="+json.list.list.questionlist_id;
          }
        return false;
        })
      }
    
    $('.ui-dialog-titlebar').hide();
    $('#cancelNewList').unbind('click').click(function(){
      $('#newQuestionList').dialog("close");
      return false;
    });
    $('#newQuestionList form').unbind('sbumit').submit(function(){
      if($('#newQuestionList form input[name=newname]').val() == "")
        {
        midas.createNotice("Please set a name.", 5000, "error");
        return false;
        }
      return true;
    })
    }
    
  // New/Edit Topic Form and popup
  var initTopicForm = function(newTopic)
    {
    $('#newTopic').dialog();
    if(newTopic)
      {
      $("#newTopic input[name=oldtopic]").val("");
      $("#newTopic input[name=topicname]").val("");
      $("#newTopic textarea[name=topicdescription]").val("");
      $("#newTopic h3:first").show();
      $("#newTopic h3:last").hide();
      $("#deleteTopic").hide();
      }
    else
      {
      $("#newTopic input[name=oldtopic]").val(json.topic.topic_id);
      $("#newTopic input[name=topicname]").val(json.topic.name);
      $("#newTopic textarea[name=topicdescription]").val(json.topic.description);      
      $("#newTopic h3:first").hide();
      $("#newTopic h3:last").show();
      $("#deleteTopic").show().unbind("click").click(function(){
        if(confirm("Do you want to delete the topic?"))
          {
          window.location.href = json.global.webroot+"/reviewosehra/admin/questions?deleteTopicId="+json.topic.topic_id;
          }
        return false;
        })
      }
    
    $('.ui-dialog-titlebar').hide();
    $('#cancelNewTopic').unbind('click').click(function(){
      $('#newTopic').dialog("close");
      return false;
    });
    $('#newTopic form').unbind('sbumit').submit(function(){
      if($('#newTopic form input[name=topicname]').val() == "")
        {
        midas.createNotice("Please set a name.", 5000, "error");
        return false;
        }
      return true;
    })
    }
    
  // New/Edit qUESTION Form and popup
  var initQuestionForm = function(newQuestion)
    {
    $('#newQuestion').dialog();
    if(newQuestion)
      {
      $("#newQuestion input[name=oldquestion]").val("");
      $("#newQuestion textarea[name=questiondescription]").val("");
      $("#newQuestion input[type=checkbox]").attr('checked',true);
      $("#newQuestion h3:first").show();
      $("#newQuestion h3:last").hide();
      $("#deleteQuestion").hide();
      }
    else
      {
      $("#newQuestion input[name=oldquestion]").val(json.question.topic_id);
      $("#newQuestion textarea[name=questiondescription]").val(json.question.description);
      $("#newQuestion input[name=questioncomment]").attr('checked',json.question.comment == "1");
      $("#newQuestion input[name=questionattachfile]").attr('checked',json.question.attachfile == "1");
      $("#newQuestion h3:first").hide();
      $("#newQuestion h3:last").show();
      $("#deleteQuestion").show().unbind("click").click(function(){
        if(confirm("Do you want to delete the question?"))
          {
          window.location.href = json.global.webroot+"/reviewosehra/admin/questions?deleteQuestionId="+json.question.question_id;
          }
        return false;
        })
      }
    
    $('.ui-dialog-titlebar').hide();
    $('#cancelNewQuestion').unbind('click').click(function(){
      $('#newQuestion').dialog("close");
      return false;
    });
    $('#newQuestion form').unbind('sbumit').submit(function(){
      if($('#newQuestion form textarea[name=questiondescription]').val() == "")
        {
        midas.createNotice("Please set a question.", 5000, "error");
        return false;
        }
      return true;
    })
    }
    
    
  if(typeof json.question != "undefined")
    {
    initQuestionForm(false);
    }
});