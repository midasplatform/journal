$(document).ready(function(){
   registerForm();
});


function registerForm()
{
  $('#registerShare').remove()
  $('div.loginElement').append('<div id="registerShare"><img id="loadingImg" style="display:none;" alt="" src="'+json.global.webroot+'/core/public/images/icons/loading.gif"  /></div>')
  $('#loadingImg').show();
      $.ajax({
          url: $('.webroot').val()+"/user/register",
          contentType: "application/x-www-form-urlencoded;charset=UTF-8",
          success: function(data) {
              $('div#registerShare').html(data).find('form#registerForm').css('color', 'black').css('margin-left','18px');
              $('#registerShareTitle').show();
              $('#email').focus();
          }
      });
}