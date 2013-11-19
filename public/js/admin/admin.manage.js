$(document).ready(function(){
  $( "#tabs" ).tabs();
  
  $('.buttomMove').click(function(){
    var moveTo  = $(this).parents('.reviewWrapper').find('.selectMove').val();
    window.location.href = $(this).attr('href')+moveTo;
  })
  
  $('.buttomPhase').click(function(){
    var setphase  = $(this).parents('.tabsWrapper').find('.selectPhase').val();
    window.location.href = $(this).attr('href')+setphase;
  })
});