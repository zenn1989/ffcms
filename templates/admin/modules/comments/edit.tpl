{% import 'macro/notify.tpl' as notifytpl %}
<script src="{{ system.script_url }}/resource/wysibb/jquery.wysibb.js"></script>
<link rel="stylesheet" href="{{ system.script_url }}/resource/wysibb/theme/default/wbbtheme.css" />
<script>
    $(document).ready(function () {
        $(".wysibb-editor").wysibb({img_uploadurl: "{{ system.script_url }}/api.php?iface=front&object=wysibbimage&dir=comment", lang: "{{ system.lang }}"})
    });
</script>
<h1>{{ extension.title }}<small>{{ language.admin_modules_comment_manage_title }}</small></h1>
<hr />
{% include 'modules/comments/menu_include.tpl' %}
{% if notify.comment_saved %}
    {{ notifytpl.success(language.admin_modules_comment_edited_success) }}
{% endif %}
<div class="row">
    <div class="col-lg-12">
        <form action="" method="post">
            <textarea name="comment_text" class="wysibb-editor" rows="7">{{ comments.text }}</textarea><br/>
            <input type="submit" name="save_comment" value="{{ language.admin_modules_comment_save_button }}" class="btn btn-success"/>
        </form>
    </div>
</div>