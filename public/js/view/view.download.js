// When page ready
$(document).ready(function(){

  if($('#disclaimerWrapperLicense').length != 0)
  {
    $.fancybox.open([
    {
      href : '#disclaimerWrapperLicense',
      closeBtn:false,
      helpers: {
        overlay: {closeClick: false}
      },
      keys : {
        close  : null
      },
      afterClose: function()
        {
        if($('#disclaimerWrapper').length != 0)
        {
          $.fancybox.open([
          {
            href : '#disclaimerWrapper',
            closeBtn:false,
            helpers: {
              overlay: {closeClick: false}
            },
            keys : {
              close  : null
            }
          }]);
        }
      }
    }]);
  }


});

