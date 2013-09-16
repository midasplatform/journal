// When page ready
$(document).ready(function(){
  // Create the root html element of each tree
  $.each(json.trees, function(key, tree)
    {
    $('#treeWrapper').append('<div class="TreeEntry"><h4>'+tree.title+' <a class="AddRootLink" href="#">(Add root Category)</a> <a class="DeleteRootLink" href="#">(Delete Tree)</a></h4><div id="categoryTree-'+tree.key+'" class="categoryTree"></div>'+$('#AddCategoryTemplate').html().replace('{parent}', tree.key));
    $(".AddRootLink:last").click(function(){
      // Show/Hide form
      $(this).parents('div.TreeEntry').find('.AddRootCategory').toggle();      
    })
    $(".DeleteRootLink:last").click(function(){
      if(confirm('Do you  really want to delete the  tree?'))
        {
        $.post(json.global.webroot+"/journal/admin/categories", {deleteChild: tree.key}, function(){
          window.location.href= json.global.webroot+"/journal/admin/categories";
        });
        } 
    })
    
    /* Init trees */
    $("div.categoryTree:last").dynatree({
        checkbox: false,
        selectMode: 3,
        children: tree.children,
        onActivate: function(node) {
           // Init the edition form
           var treeId = node.tree.divTree.attributes.id.nodeValue;
           var form = $('#'+treeId).parents('div.TreeEntry').find('.AddCategory');
           form.show()
           form.find(".parentCategory").val(node.data.key)
           form.find(".ParentName").html(node.data.title)
           form.find(".deleteCategory").click(function(){
             if(confirm('Do you  really want to delete the category and its children?'))
               {
               $.post(json.global.webroot+"/journal/admin/categories", {deleteChild: node.data.key}, function(){
                 window.location.href= json.global.webroot+"/journal/admin/categories";
               });
               }
           });
        },
        // The following options are only required, if we have more than one tree on one page:
        //        initId: "treeData",
        cookieId: "dynatreeEdit-"+key,
        idPrefix: "dynatreeEdit-"+key
      });
    }
  );
  
});