{$jsurl {$url}/resource/wysibb/jquery.wysibb.min.js}
{$cssurl {$url}/resource/wysibb/theme/default/wbbtheme.css}
<script>
    $(document).ready(function () {
        $(".wysibb-editor").wysibb({img_uploadurl: "{$url}/api.php?action=commentupload"})
    });
</script>
<br/><br/>
<div>
    <textarea style="width: 95%;" placeHolder="{$lang::comments_text_writemsg}" id="comment_value" class="wysibb-editor"
              rows="5"></textarea>
    <a href="#comment-add" id="comment_send" class="btn btn-success pull-right"/>{$lang::global_send_button}</a>
</div>
<br/><br/>
<div id="edit-comment" class="modal hide fade large">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>{$lang::comment_modal_edit_header}</h3>
    </div>
    <div class="modal-body">
        {$if user.auth && user.admin}
        <div class="alert alert-info">{$lang::comment_modal_text_notify}</div>
        {$/if}
        <div id="comment-edit-jquery">{$lang::comment_modal_edit_loadingnow}</div>
    </div>
</div>
