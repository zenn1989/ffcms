function replayto(username) {
    var comment_dom = $('#comment_value');
    var comment_value = comment_dom.val();
    comment_dom.val('[b]' + username + '[/b], ' + comment_value);
}

function editcomment(id) {
    $.get(
        host+'/api.php?iface=front&object=commentedit&id='+id, function(data) {
        $('#comment-edit-jquery').html(data);
    });
}

function saveeditedcomment(id) {
    $('#edit-area-comment').sync();
    var comment_textvalue = $('#edit-area-comment').val();
    $.post
    (
        host+'/api.php?iface=front&object=commentsave',
        {comment_id : id, comment_text : comment_textvalue}, function() {
            location.reload();
        }
    );
}

function deletecomment(id) {
    if(confirm('Delete?'))
    {
        $.get(
            host+'/api.php?iface=front&object=commentdelete&id='+id, function() {
                location.reload();
            }
        );
    }
}

function changeKarma(type, target_id, url) {
    $.get(host + '/api.php?iface=front&object=userkarma&make=change&type='+type+'&target='+target_id+'&url='+url, function(response){
        if(response == 'error') {
            alert('Error');
        } else {
            $('#karmabutton').html(response);
        }
    });
}

$(document).ready(function() {
    $('#doLoadComment').click(function() {
        if(!current_point)
            current_point = 1;
        $.post(host+'/api.php?iface=front&object=commentview',
            {pathway : comment_pathway, comment_position : ++current_point },
            function(data) {
                $('#comment_list').html(data);
            });
    });
    $('#doLoadAllComment').click(function() {
        $.post(host+'/api.php?iface=front&object=commentview',
            {pathway : comment_pathway, comment_all : true},
            function(data) {
                $('#comment_list').html(data);
                $('#comment_load').hide();
            });
    });
    $('#comment_send').click(function(e){
        $('#comment_value').sync();
        var comment_text = $('#comment_value').val();
        var guest_name = $('[name=guest_name]').val();
        var captcha = $('[name=captcha]').val();
        var recaptcha_challenge_field = $('[name=recaptcha_challenge_field]').val();
        var recaptcha_response_field = $('[name=recaptcha_response_field]').val();
        if(comment_text.length > 0)
        {
            $.post(host+'/api.php?iface=front&object=commentpost',
                { comment_message : comment_text, comment_position : current_point, pathway : comment_pathway,
                  guest_name : guest_name, captcha : captcha, recaptcha_challenge_field : recaptcha_challenge_field, recaptcha_response_field : recaptcha_response_field
                },
                function(data) {
                    $('#comment_list').html(data);
                });
            if(guest_name != null && guest_name.length > 0)
                $('#comment-guest').modal('hide');
            $('#comment_value').htmlcode('');
        }
    });
    $( ".side-links .toggle-children" ).click(function() {
        $(this).toggleClass('open').next().toggle("slow");
    });
    $('.tooltip-show').tooltip();
    // run cron tasker in user background
    $.get(host+'/api.php?cron='+Math.random());
});

function ffcmsAddBookmark(b_url, b_title) {
    $.post(host+'/api.php?iface=front&object=bookmarkadd', { url : b_url, title : b_title});
    return false;
}

/** Hide broken images */
$(window).load(function() {
    $("img").each(function(){
        var image = $(this);
        if(image.context.naturalWidth == 0 ||
            image.readyState == 'uninitialized'){
            $(image).unbind("error").hide();
        }
    });
});

$(document).ready(function() {
    var subject_id;
    $('.answershow').click(function(e) {
        subject_id = e.target.id;
        $.get(host+'/api.php?iface=front&object=wallview&id='+subject_id, function(data) {
            $('#wall-jquery').html(data);
        });
    });
    $('#addanswer').click(function(e){
        var answer_text = $('#answer').val();
        if(answer_text.length > 0)
        {
            $.post(host+'/api.php?iface=front&object=wallpost&id='+subject_id, { message : answer_text}, function(data){
                $('#wall-jquery').html(data);
            });
            $('#answer').val(null);
        }
    });
});