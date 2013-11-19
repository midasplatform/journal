$(document).ready(function(){
  
  if($('.hiddenReview').length != 0)
    {
    $('#hiddenReviewlink').show();
    $('#hiddenReviewlink').html('Show '+$('.hiddenReview').length+" hidden review");
    if($('.hiddenReview').length > 1)$('#hiddenReviewlink').append('s');
    
    $('#hiddenReviewlink').click(function(){
      $(this).remove();
      $('.hiddenReview').show();
    })
    }
  
  });