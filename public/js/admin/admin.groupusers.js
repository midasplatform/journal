// When page ready
$(document).ready(function(){
  
  // Remove user from the editor list
  $('a.removeMember').click(function(){
    var key = $(this).attr('key');
    var html = '';
    html += 'Are you sure you want to remove the editor?';
    html += '<br/>';
    html += '<br/>';
    html += '<br/>';
    html += '<span style="float: right">';
    html += '<input class="globalButton removeUserYes" type="button" value="'+json.global.Yes+'"/>';
    html += '<input style="margin-left:15px;" class="globalButton removeUserNo" type="button" value="'+json.global.No+'"/>';
    
    var userId = $(this).attr('userId');
    
    var group = json.editorgroup.group_id;
    if(key == "member") group = json.membergroup.group_id;

    midas.showDialogWithContent('Remove', html, false);
    $('input.removeUserYes').unbind('click').click(function () {
        $('div.MainDialogContent').html("Please wait...");
        $.post(json.global.webroot+'/journal/admin/groupusers', {remove: 1, groupId: group, userId: userId},
        function(data) {
            window.location.reload();
        }
        );
    });
    $('input.removeUserNo').unbind('click').click(function() {
        $('div.MainDialog').dialog('close');
    });
  })
  
  
  /// Search user
  $.widget( "custom.catcomplete", $.ui.autocomplete, {
      _renderMenu: function( ul, items ) {
          var self = this, currentCategory = "";
          $.each(items, function (index, item) {
              if(item.category != currentCategory) {
                  ul.append('<li class="search-category">' + item.category + "</li>" );
                  currentCategory = item.category;
              }
              self._renderItemData( ul, item );
          });
      }
  });

  var invitationSearchcache = {}, lastShareXhr;

  $(".live_invitation_search").catcomplete({
      minLength: 2,
      delay: 10,
      source: function (request, response) {
          var term = request.term;
          if(term in invitationSearchcache) {
              response(invitationSearchcache[term]);
              return;
          }
          var loadingObj = $(this).parents('.invitationSearch').find(".searchInvitationLoading");
          loadingObj.show();

          lastShareXhr = $.getJSON( $('.webroot').val()+"/api/json?method=midas.journal.usersearch",
            request, function(data, status, xhr) {
              
              loadingObj.hide();
              invitationSearchcache[term] = data.data;
              if(xhr === lastShareXhr) {
                  response(data.data);
              }
          });
      }, // end source
      select: function (event, ui) {
          var key = $(this).attr('key');
          var html = '';
          html += 'Are you sure you want to add '+ui.item.value+"? ";
          html += '<br/>';
          html += '<br/>';
          html += '<br/>';
          html += '<span style="float: right">';
          html += '<input class="globalButton removeUserYes" type="button" value="'+json.global.Yes+'"/>';
          html += '<input style="margin-left:15px;" class="globalButton removeUserNo" type="button" value="'+json.global.No+'"/>';

          midas.showDialogWithContent('Add', html, false);
          $('input.removeUserYes').unbind('click').click(function () {
              $('div.MainDialogContent').html("Please wait...");
              
              var group = json.editorgroup.group_id;
              if(key == "member") group = json.membergroup.group_id;
              
              $.post(json.global.webroot+'/journal/admin/groupusers',
              {add: 1, groupId: group, userId: ui.item.userid},
              function(data) {
                 window.location.reload();
              }
              );
          });
          $('input.removeUserNo').unbind('click').click(function() {
              $('div.MainDialog').dialog('close');
          });
      } //end select
  });

  $('.live_invitation_search').focus(function () {
      var objValue = $(this).parents('.invitationSearch').find('.live_invitation_search_value');
      if(objValue.val() == 'init') {
          objValue.val($(this).val());
          $(this).val('');
      }
  }).focusout(function () {
      var objValue = $(this).parents('.invitationSearch').find('.live_invitation_search_value');
      if($(this).val() == '') {
          $(this).val(objValue.val());
          objValue.val('init');
      }
  });

  
});