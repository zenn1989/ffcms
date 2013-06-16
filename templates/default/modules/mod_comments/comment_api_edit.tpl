<div>
    <textarea style="width: 95%;height: 200px;" class="wysibb-editor" rows="5" id="edit-area-comment">{$comment_text}</textarea>
    <button class="btn btn-success" onclick="saveeditedcomment({$comment_id})">{$lang::global_send_button}</button>
</div>
<script>
    $(".wysibb-editor").wysibb();
</script>