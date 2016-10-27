
$(document).ready(function(){
    $('#acceptRights').change(function(){
      submitCheck();
    })
    
    $('#acceptRights').change();
    
    $('#acceptAttributionPolicy').change(function(){
      var acceptAttributionPolicyIsSelected = $(this).is(":visible") && $(this).is(':checked');
      $('#hiddenAttributionPolicy').attr('value', acceptAttributionPolicyIsSelected ? 1 : 0);
      submitCheck();
    })
    
    $('#acceptAttributionPolicy').change();
    
    $('#acceptLicense').change(function(){
      var license = $("#licenseChoice").val();
      $('#hiddenSourceLicense').attr('value', $(this).is(':checked') ? license: 0);

      if(license == 1 && $(this).is(':checked'))
        {
        $('#acceptAttributionPolicy').show();
        $('#acceptAttributionPolicyLabel').show();
        }
      else
        {
        $('#acceptAttributionPolicy').hide();
        $('#acceptAttributionPolicyLabel').hide();
        }
      if(license==3 && $(this).is(':checked'))
        {
        $("#otherLicenseInput").show();
        $("#otherLicenseInputLabel").show();
        }
      else
        {
        $("#otherLicenseInput").hide();
        $("#otherLicenseInputLabel").hide();
        }
      var acceptAttributionPolicyIsSelected = $('#acceptAttributionPolicy').is(":visible") && $('#acceptAttributionPolicy').is(':checked');
      $('#hiddenAttributionPolicy').attr('value', acceptAttributionPolicyIsSelected ? 1 : 0);
      submitCheck();
    });

    $('#licenseChoice').change(function(){
      var license = $(this).val();
      $('#hiddenSourceLicense').attr('value', $('#acceptLicense').is(':checked') ? license: 0);

      if(license == 1 && $('#acceptLicense').is(':checked'))
        {
        $('#acceptAttributionPolicy').show();
        $('#acceptAttributionPolicyLabel').show();
        }
      else
        {
        $('#acceptAttributionPolicy').hide();
        $('#acceptAttributionPolicyLabel').hide();
        }

      if(license==3 && $('#acceptLicense').is(':checked'))
        {
        $("#otherLicenseInput").show();
        $("#otherLicenseInputLabel").show();
        }
      else
        {
        $("#otherLicenseInput").hide();
        $("#otherLicenseInputLabel").hide();
        }
      var acceptAttributionPolicyIsSelected = $('#acceptAttributionPolicy').is(":visible") && $('#acceptAttributionPolicy').is(':checked');
      $('#hiddenAttributionPolicy').attr('value', acceptAttributionPolicyIsSelected ? 1 : 0);
      submitCheck()
      });
   
    $('#licenseChoice').change();
    

    $('#sendNotificationEmail').change(function(){
      var sendNotificationEmailIsSelected = $(this).is(":visible") && $(this).is(':checked');
      $('#hiddenSendNotificationEmail').attr('value', sendNotificationEmailIsSelected ? 1 : 0);
    })

    $('#sendNotificationEmail').change();
    // Introduce free text license change

    $("#otherLicenseInput").change(function(){
    var otherLicenseIsFilled = $("#otherLicenseInput").is(":visible") && $("#otherLicenseInput").val();
    $('#hiddenSourceLicenseText').attr('value', otherLicenseIsFilled ? $("#otherLicenseInput").val() : "Other");
    submitCheck();
    })
    // Set up change function to run when text area loses focus
    $("#otherLicenseInput").change()

    $('#typeFile').change(function(){      
      if($(this).val() == 6)
        {
        $('#githubContentBlock').show();
        $('#progress').hide();
        $('#uploadContentBlock').hide();
        }
      else
        {
        $('#githubContentBlock').hide();
        $('#uploadContentBlock').show();
        }
      });
    $('#fileupload').fileupload({
        url: json.global.webroot+"/journal/submit/uploadhandler",
        dataType: 'json',
        start: function(e, data){
          $('#typeFile').attr('disabled', true);
          $('#progress').show();
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<p/>').text(file.name).appendTo('#files');
            });
        },
        stop: function (e, data) {
          window.location.href=json.global.webroot+"/journal/submit/upload?processUpload=true&revisionId="+json.revision+"&type="+$('#typeFile').val();
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css(
                'width',
                progress + '%'
            );
        }
    });
    // Create delete event
    $('a.deleteLink').click(function(){
      if(confirm("Do you want to delete the file?"))
        {
        $.post(json.global.webroot+"/journal/submit/upload?revisionId="+json.revision, {deletebitstream: $(this).parents('tr').attr('key')},function(){
          window.location.reload();
        });        
        }
    })
    
    // Add github repository
    $('#addGithub').click(function(){
      $.post(json.global.webroot+"/journal/submit/addgithubhandler?revisionId="+json.revision, {github: $("#github").val()},function(retVal){
          if(retVal[0] == 1) window.location.reload();
          else alert(retVal[1]);
        }, 'json');        
    })
    
  KeepAlive();
});

function submitCheck()
  {
  $('input[type=submit]').attr('disabled',!$('#acceptRights').is(':checked') || !$('#acceptLicense').is(':checked') ||
                                        ($('#acceptAttributionPolicy').is(":visible") && !$('#acceptAttributionPolicy').is(':checked')) ||
                                        ($("#otherLicenseInput").is(":visible") && !$("#otherLicenseInput").val() ));
  }
function KeepAlive()
  {
  $.get(json.global.webroot+'/journal/help', function(data) { });
  setTimeout("KeepAlive()", 1000 * 60 * 5);
  }
