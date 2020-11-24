let imgClass;

function showModal(url, classname, title, width, height) {
    imgClass = classname;
    $('#modal-iframe').attr("src", url);
    $('.modal-vertical-centered').css('width', width);
    $('.modal-vertical-centered').css('height', height);
    $('.modal-title').text(title);

    $('.centered-modal').attr('data-class', classname);
    let modal = $('.centered-modal').modal({
        show: true,
        keyboard: true, backdrop: false
    }).on('hidden.bs.modal', function () {
        $(this).find('iframe').html("").attr("src", "");
    });
    modal.find('#iframe-loading').show();
    modal.find('iframe').on("load", function () {
        modal.find('#iframe-loading').hide();
    });
}

function clearTest(name, full) {
    $('.centered-modal').modal('hide');
    let inputClassName = "input." + imgClass;
    $(inputClassName).fileinput('clear');
    $(inputClassName).fileinput('disable');

    let url = ($('input.' + imgClass).data('url') == 'fullurl') ? full : name;

    $(inputClassName).parent().parent().prev().children('.file-caption-name').text(url);
    $(inputClassName).parent().parent().prev().children('.file-caption-name').attr('placeholder', url);
    // $('.'+materialPR + imgClass).val(url);
    $("input[name='" + materialPR + imgClass + "']").val(url);
    // $("input[name='" + materialPR + imgClass + "']").attr('placeholder', url);

}

