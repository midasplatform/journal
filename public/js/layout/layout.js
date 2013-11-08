var json;
var itemselected = false;
var midas = midas || {};
var obj = null;
var inMenu = false;

// Prevent error if console.log is called
if (typeof console != "object") {
  var console = {
    'log': function() {}
  };
}

// Main calls
$(function() {

  // Parse json content
  json = jQuery.parseJSON(rawJson);

  // Init Dynamic help ---------------
  InitHelpQtip();
  if(json.global.dynamichelpAnimate)
    {
    TimerQtip();
    }
  else
    {
    StopTimerQtip();
    }

  // Search Bar -----------------------
  // Live search
  
  if(json.searchDisableCompletion == undefined)
    {
    $('#live_search').keyup(function(e)
     {
     if(e.keyCode == 13 && !itemselected) // enter key has been pressed
       {
       window.location = $('.webroot').val()+'/journal?q='+encodeURI($('#live_search').val());
       }
     });
    }

  $('#live_search').focus(function() {
    if($('#live_search_value').val() == 'init')
      {
      $('#live_search_value').val($('#live_search').val());
      $('#live_search').val('');
      }
    });

  $('#live_search').focusout(function() {
    if($('#live_search').val() == '')
      {
      $('#live_search').val($('#live_search_value').val());
      $('#live_search_value').val('init');
      }
      });
       

 $('#Nav > li').hover(function() {  

  if (obj && !inMenu) { 
   obj.find('ul').fadeOut('fast');
   obj = null;
  } //if
 
  $(this).find('ul').fadeIn('fast');
 }
 , function() {
  obj = $(this);
  setTimeout(
   "checkHover()",
   400);
 });

 $('#Nav > li > ul ').hover(function() {  
   inMenu = true;
  }
  , function() {
    inMenu = false;
  });


    // Register link
  $("a.register").click(function()
    {
    midas.showOrHideDynamicBar('register');
    midas.loadAjaxDynamicBar('register','/user/register');
    return false;
    });
});


function checkHover() {
 if (obj && !inMenu) {
  obj.find('ul').fadeOut('fast');
 } //if
} //checkHover


 // Javascript uilts ----------------------------------
 var qtipsHelp = new Array();
 var iQtips = 0;
 
 function FixTreeObjects(tree)
  {
  tree.select = tree.select == 1;
  $.each(tree.children, function(index, value){
    tree.children[index] = FixTreeObjects(value);
  });
  return tree;
  }

 function InitHelpQtip()
   {
   if(!json.global.dynamichelp)return ;
   if(json.dynamicHelp == undefined)return;
   $.each(json.dynamicHelp, function(index, value) {
         var text = value.text;
         text = text.replace(/&lt;/g, '<');
         text = text.replace(/&gt;/g, '>');
         var tmp = $(value.selector).qtip({
           content: {
              text: text
           },
           position: {
              my: value.my,  // Position my top left...
              at: value.at // at the bottom right of...
           }
        });
        qtipsHelp.push(tmp);
     });
   }

 // Dynamic help sequence
 function TimerQtip() {
       if(!json.global.dynamichelp)return ;

       $.each(qtipsHelp, function(index, value) {
         value.qtip('hide');
         value.qtip('disable');
        });

        if(json.global.demomode)
          {
          $('.loginLink').qtip('enable');
          }

       if(!$('#dialogStartingGuide').is(':hidden'))
         {
         iQtips = 0;
         setTimeout("TimerQtip()",1000);
         return;
         }

       qtipsHelp[iQtips].qtip('show');
       if(qtipsHelp.length > iQtips+1)
         {
         setTimeout("TimerQtip()",5000);
         }
       else
         {
         setTimeout("StopTimerQtip()",5000);
         }
       iQtips++;
     }

  function StopTimerQtip()
    {
      if(!json.global.dynamichelp)return ;
      $.each(qtipsHelp, function(index, value) {
         value.qtip('hide');
         value.qtip('enable');
        });
    }

/** Login management */
var midas = midas || {};
midas.user = midas.user || {};
midas.user.login = midas.user.login || {};

midas.user.login.validateLoginForm = function () {
    $('input[name=previousuri]').val(json.global.currentUri);
    if($('#password').val() == '') {
        midas.createNotice('Password field must not be empty', 3500, 'error');
        return false;
    }
    $('#loginForm input[type=submit]').attr('disabled', 'disabled');
    $('#loginWaiting').show();
};

midas.user.login.loginResult = function (responseText) {
    'use strict';
    $('#loginWaiting').hide();
    $('#loginForm input[type=submit]').removeAttr('disabled');
    try {
        var resp = $.parseJSON(responseText);
        if(resp.status && resp.redirect) {
            window.location.href = json.global.webroot+"/journal";
        }
        else if(resp.dialog) {
            midas.loadDialog('loginOverride', resp.dialog);
            midas.showDialog(resp.title, false, resp.options);
        }
        else {
            alert(resp.message)
        }
    } catch(e) {
        midas.createNotice('An internal error occured, please contact your administrator',
                           5000, 'error');
    }
};

$(document).ready(function () {
    'use strict';
    if($('form#loginForm').length != 0)
      {
      $('form#loginForm').ajaxForm({
          beforeSubmit: midas.user.login.validateLoginForm,
          success: midas.user.login.loginResult
      });

      // Deal with password recovery
      $('a#forgotPasswordLink').click(function () {
          midas.loadDialog("forgotpassword", "/user/recoverpassword");
          midas.showDialog("Recover Password");
      });

      $("a.registerLink").unbind('click').click(function () {
          midas.showOrHideDynamicBar('register');
          midas.loadAjaxDynamicBar('register', '/user/register');
      });
      }

});
