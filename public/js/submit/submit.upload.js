
$(document).ready(function(){
    $('#acceptRights').change(function(){
      $('input[type=submit]').attr('disabled', !$(this).is(':checked') || 
                                            ($('#acceptAttributionPolicy').is(":visible") && !$('#acceptAttributionPolicy').is(':checked')));
    })
    
    $('#acceptRights').change();
    
    $('#acceptAttributionPolicy').change(function(){
      var acceptAttributionPolicyIsSelected = $(this).is(":visible") && $(this).is(':checked');
      $('#hiddenAttributionPolicy').attr('value', acceptAttributionPolicyIsSelected ? 1 : 0);

      $('input[type=submit]').attr('disabled', ($(this).is(":visible") && !$(this).is(':checked')) || !$('#acceptRights').is(':checked'));
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

      var acceptAttributionPolicyIsSelected = $('#acceptAttributionPolicy').is(":visible") && $('#acceptAttributionPolicy').is(':checked');
      $('#hiddenAttributionPolicy').attr('value', acceptAttributionPolicyIsSelected ? 1 : 0);

      $('input[type=submit]').attr('disabled', !$('#acceptRights').is(':checked') || 
                                            ($('#acceptAttributionPolicy').is(":visible") && !$('#acceptAttributionPolicy').is(':checked')));
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

      var acceptAttributionPolicyIsSelected = $('#acceptAttributionPolicy').is(":visible") && $('#acceptAttributionPolicy').is(':checked');
      $('#hiddenAttributionPolicy').attr('value', acceptAttributionPolicyIsSelected ? 1 : 0);

      $('input[type=submit]').attr('disabled', !$('#acceptRights').is(':checked') || 
                                            ($('#acceptAttributionPolicy').is(":visible") && !$('#acceptAttributionPolicy').is(':checked')));
      });
   
    $('#licenseChoice').change();
    
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


function KeepAlive()
  {
  $.get(json.global.webroot+'/journal/help', function(data) { });
  setTimeout("KeepAlive()", 1000 * 60 * 5);
  } 