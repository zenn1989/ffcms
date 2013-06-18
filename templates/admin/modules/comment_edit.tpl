{$jsurl {$url}/resource/wysibb/jquery.wysibb.min.js}
{$cssurl {$url}/resource/wysibb/theme/default/wbbtheme.css}
<script>
    $(document).ready(function() {
        $(".wysibb-editor").wysibb()
    });
</script>
{$notify}
<form action="" method="post">
    <textarea name="comment_text" class="wysibb-editor" rows="7">{$comment_text}</textarea><br />
    <input type="submit" name="save_comment" value="Сохранить" class="btn btn-success" />
</form>