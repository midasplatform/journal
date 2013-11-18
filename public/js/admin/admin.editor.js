// When page ready
$(document).ready(function(){
  
  // Remove user from the editor list
  $('a.removeMember').click(function(){
    var html = '';
    html += 'Are you sure you want to remove the editor?';
    html += '<br/>';
    html += '<br/>';
    html += '<br/>';
    html += '<span style="float: right">';
    html += '<input class="globalButton removeUserYes" type="button" value="'+json.global.Yes+'"/>';
    html += '<input style="margin-left:15px;" class="globalButton removeUserNo" type="button" value="'+json.global.No+'"/>';
    
    var userId = $(this).attr('userId');

    midas.showDialogWithContent('Remove editor', html, false);
    $('input.removeUserYes').unbind('click').click(function () {
        $('div.MainDialogContent').html("Please wait...");
        $.post(json.global.webroot+'/community/removeuserfromgroup', {groupId: json.group.group_id, userId: userId},
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

  $("#live_invitation_search").catcomplete({
      minLength: 2,
      delay: 10,
      source: function (request, response) {
          var term = request.term;
          if(term in invitationSearchcache) {
              response(invitationSearchcache[term]);
              return;
          }
          $("#searchInvitationLoading").show();

          lastShareXhr = $.getJSON( $('.webroot').val()+"/search/live?userSearch=true&allowEmail",
            request, function(data, status, xhr) {
              $("#searchInvitationLoading").hide();
              invitationSearchcache[term] = data;
              if(xhr === lastShareXhr) {
                  response(data);
              }
          });
      }, // end source
      select: function (event, ui) {
          var html = '';
          html += 'Are you sure you want to add '+ui.item.value+"? ";
          html += '<br/>';
          html += '<br/>';
          html += '<br/>';
          html += '<span style="float: right">';
          html += '<input class="globalButton removeUserYes" type="button" value="'+json.global.Yes+'"/>';
          html += '<input style="margin-left:15px;" class="globalButton removeUserNo" type="button" value="'+json.global.No+'"/>';

          midas.showDialogWithContent('Remove editor', html, false);
          $('input.removeUserYes').unbind('click').click(function () {
              $('div.MainDialogContent').html("Please wait...");
              $.post(json.global.webroot+'/community/manage?addUser=true&communityId='+json.community.community_id,
              {groupId: json.group.group_id, users: ui.item.userid},
              function(data) {
                 window.location.reload();
              }
              );
          });
          $('input.removeUserNo').unbind('click').click(function() {
              $('div.MainDialog').dialog('close');
          });
          showGroupSelect(ui.item);
      } //end select
  });

  $('#live_invitation_search').focus(function () {
      if($('#live_invitation_search_value').val() == 'init') {
          $('#live_invitation_search_value').val($(this).val());
          $(this).val('');
      }
  }).focusout(function () {
      if($(this).val() == '') {
          $(this).val($('#live_invitation_search_value').val());
          $('#live_invitation_search_value').val('init');
      }
  });

  
});