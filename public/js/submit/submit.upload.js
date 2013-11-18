
$(document).ready(function(){
    $('#acceptRights').change(function(){
      $('input[type=submit]').attr('disabled', !$(this).is(':checked'));
    })
    
    $('#typeFile').change(function(){$('#uploadContentBlock').show()});
    $('#fileupload').fileupload({
        url: json.global.webroot+"/journal/submit/uploadhandler",
        dataType: 'json',
        start: function(e, data){
          $('#typeFile').attr('disabled', true);
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
    
});
