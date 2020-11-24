$(document).ready(function(){
     $('.icoNavIn').click(function(event) {
          $(this).toggleClass('on');
          $('.mbMenuArea').toggle();
           $('.mbSearchInpArea').hide(); 
          

      });   
     $('.mbSearchBtn').click(function(event) {
          $('.mbSearchInpArea').toggle();
           $('.mbMenuArea').hide(); 
          $('.icoNavIn').removeClass('on');
          $('.mbForm input').focus();
      });   

    $('.searchBtn').click(function(){
      $('.pcInpArea').show();
      $('.pcSearchInp input').focus();
    });

    $('.pcCloseInp').click(function(){
      $('.pcInpArea').hide();
      $('.pcSearchInp input').blur();
    });

    $('.entHeadTabLi').click(function(){
        var index = $(this).index();
        $(this).addClass('entHeadCur').siblings().removeClass('entHeadCur')
        $('.entConArea .entConIn').eq(index).show().siblings().hide();
    });
     function stopBubble(con){
        $(con).click(function(e){
            e.stopPropagation();
        });
     }
    $(document).bind('click',function(){
          $('.mbMenuArea').hide();
          $('.icoNavIn').removeClass('on');
          $('.mbSearchInpArea').hide();
    });
    stopBubble(".icoNavIn");
    stopBubble(".mbSearchInpArea");
    stopBubble(".mbSearchBtn");

    $('.packSwitch').hover(function() {
      $('.secondMenu').show();
    }, function() {
      $('.secondMenu').hide();
    });

    $('.loginDelBtn').click(function(event) {
      $('.loginArea').hide();
    });
});