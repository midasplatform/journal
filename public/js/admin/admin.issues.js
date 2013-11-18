// When page ready
$(document).ready(function(){
  
   $('a.createCommunity').click(function () {
        $.ajax({
              url: $('.webroot').val() + "/community/create",
              //contentType: "application/x-www-form-urlencoded;charset=UTF-8",
              success: function (data) {
                  $('div.MainDialogContent').html(data);
                  $('div.MainDialogLoading').hide();
                  $('.dialogTitle').hide();
                  $('div.MainDialogContent .radioElement').hide();
                  $('div.MainDialogContent form').submit(function(){
                    $.post($('.webroot').val() + "/community/create",
                      {
                      name : $('div.MainDialogContent input#name').val(),
                      description : $('div.MainDialogContent #description').val(),
                      privacy: 0,
                      canJoin: 1                      
                      }, function(){
                        window.location.reload();
                      })
                    $('div.MainDialogContent').html("Please Wait...");
                    return false;
                  });
              }
          });
        midas.showDialog("Create Journal", false);
    });
});     