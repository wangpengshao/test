void function() {

var $ = document.querySelector.bind(document);
// var myjson={weimicms::$myjson};
//--- 数据相关
// var list = [{
//     date: '自习室123123',
//     content: '<span>11.26</span>&nbsp;&nbsp;<span>3:00~4:00</span>',
//     btn :'<a href="javascript:;" class="signBtn disNone">签到</a>'
//
// }, {
//    date: '自习室1',
//     content: '<span>10.26</span>&nbsp;&nbsp;<span>3:00~4:00</span>',
//     btn :'<a href="javascript:;" class="signBtn disNone">已签到</a>'
// }, {
//    date: '自习室1',
//    content: '<span>9.26</span>&nbsp;&nbsp;<span>3:00~4:00</span>',
//    btn :'<a href="javascript:;" class="signBtn disNone continueOrderBtn">继续预约</a>'
// }, {
//    date: '自习室1',
//    content: '<span>9.26</span>&nbsp;&nbsp;<span>3:00~4:00</span>',
//    btn :'<a href="javascript:;" class="signBtn disNone">未能签到</a>'
// }];
if(list.length>0){
        var itemHtmls = [];
        list.forEach(function(item, index, arr) {
            itemHtmls.push(
                '<div class="listItem' +
                (index === 0 ? ' listItem-first' : '') +
                (index === arr.length - 1 ? ' listItem-last' : '') +
                '" data-index="' + index +'"><div class="listItemContent"><div class="listItemContent-date roomName">' +
                item.date + '</div><div class="listItemContent-content odTime"> '  + item.content +
                '</div>'+item.btn+'</div></div>');
        });
        $('#list').innerHTML = itemHtmls.join('');

        var $focusItem = $('.listItem');
        $focusItem.classList.add('highlight');
}


// 点击高亮
var closest = function(el, className) {
    if (el.classList.contains(className)) return el;
    if (el.parentNode) {
        return closest(el.parentNode, className);
    }
    return null;
};
$('#list').addEventListener('click', function(e) {
    var $listItem = closest(e.target, 'listItem');
    if ($listItem && $listItem != $focusItem) {
        $focusItem.classList.remove('highlight');
        $focusItem = $listItem;
        $focusItem.classList.add('highlight');
    }
});


}();
