<div>
    <textarea style="width: 95%;height: 200px;" class="wysibb-editor" rows="5"
              id="edit-area-comment">{{ local.comment.body }}</textarea>
    <button class="btn btn-success" onclick="saveeditedcomment({{ local.comment.id }})">{{ language.global_send_button }}</button>
</div>
<script>
    $(".wysibb-editor").wysibb();
</script>