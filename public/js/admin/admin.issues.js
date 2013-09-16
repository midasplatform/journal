// When page ready
$(document).ready(function(){
  
   $('a.createCommunity').click(function () {
        midas.loadDialog("createCommunity","/community/create");
        midas.showDialog("Create Journal", false);
    });
});