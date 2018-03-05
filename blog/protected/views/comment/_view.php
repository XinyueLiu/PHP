<?php
$deleteJS = <<<DEL
$('.container').on('click', '.time a.delete', function(){
    var th=$(this),
        container=th.closest('div.comment'),
        id=container.attr('id').slice(1);
    if(confirm('Are you sure you want to delete comment #'+id+'?')) {
        $.ajax({
            url:th.attr('href),
            type: 'POST'
        }).done(function(){container.slideUp()});
    }
});
DEL;

?>