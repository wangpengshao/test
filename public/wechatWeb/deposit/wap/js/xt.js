$(function(){
   $('.canChose').click(function(){
    $(this).addClass('chosedStage').siblings('.canChose').removeClass('chosedStage');
    $(this).parents('.dayStageLine').siblings('.dayStageLine').find('.canChose').removeClass('chosedStage');
   });

   $('.pwdLookIcon').click(function(){
      if(!$(this).hasClass('hasTap')){
         $(this).addClass('hasTap');
         $('input[name=pwdInp]').prop('type','text');
      }else{
         $(this).removeClass('hasTap');
         $('input[name=pwdInp]').prop('type','password');
      }
   });
});