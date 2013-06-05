{$jsurl {$url}/resource/wysibb/jquery.wysibb.min.js}
{$cssurl {$url}/resource/wysibb/theme/default/wbbtheme.css}
<script>
    $(document).ready(function() {
        $(".wysibb-editor").wysibb()
    });
</script>
<div>
    <textarea style="width: 95%;" placeHolder="{$lang::comments_text_writemsg}" id="comment_value" class="wysibb-editor" rows="5"></textarea>
    <a href="#comment-add" id="comment_send" class="btn btn-success pull-right" />{$lang::global_send_button}</a>
</div>
<br /><br />