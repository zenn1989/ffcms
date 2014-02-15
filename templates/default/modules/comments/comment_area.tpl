<script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/jquery.wysibb.min.js"></script>
{% if system.lang in ['ar', 'cn', 'de', 'en', 'fr', 'pl', 'tr', 'ua', 'vn'] %}
<script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/lang/{{ system.lang }}.js"></script>
{% endif %}
<link rel="stylesheet" href="{{ system.script_url }}/resource/wysibb/theme/default/wbbtheme.css" />
<script>
    $(document).ready(function () {
        $(".wysibb-editor").wysibb({img_uploadurl: "{{ system.script_url }}/api.php?iface=front&object=wysibbimage&dir=comment", lang: "{{ system.lang }}"})
    });
</script>
<script>
    var comment_pathway = null;
    var current_point = 1;
    comment_pathway = '{{ local.pathway }}';
</script>
<ul class="nav nav-tabs">
    <li class="active"><a href="#comment_site" data-toggle="tab">{{  language.comments_text_title }}</a></li>
    <!-- VK comment example :
    <li><a href="#comment_vk" data-toggle="tab">Vkontakte</a></li>
    -->
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="comment_site">
        <div style="padding-top: 25px;"> <!-- padding for wisybb popover -->
            {% if user.id > 0 %}
            <textarea style="width: 95%;" placeHolder="{{ language.comments_text_writemsg }}" id="comment_value" class="wysibb-editor" rows="5"></textarea>
            <div class="pull-right"><a href="#comment-add" id="comment_send" class="btn btn-success">{{ language.global_send_button }}</a></div><br /><br />
            {% else %}
            <p class="alert alert-warning">{{ language.comments_register_msg }}</p>
            {% endif %}
        </div>
        <div id="comment_list" class="well-item">
            {{ comments }}
        </div>
        <div id="loader_comment">
            <div id="comment_load">
                <table class="table">
                    <tr>
                        <td style="text-align: center;" class="alert alert-success"><a href="#comment_load" id="doLoadComment">{{ language.comments_text_loadmore }}</a></td>
                        <td style="text-align: center;" class="alert alert-error"><a href="#comment_load" id="doLoadAllComment">{{ language.comments_text_loadall }}</a></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <!--
    <div class="tab-pane" id="comment_vk">Code vk.com here</div>
    -->
</div>

<!-- Edit comment form pop-up modal. Id #comment-edit-jquery must be setted, used by jquery -->
<div id="edit-comment" class="modal hide fade large">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>{{ language.comment_modal_edit_header }}</h3>
    </div>
    <div class="modal-body">
        {% if user.id > 0 and user.admin %}
        <div class="alert alert-info">{{ language.comment_modal_text_notify }}</div>
        {% endif %}
        <div id="comment-edit-jquery">{{ language.comment_modal_edit_loadingnow }}</div>
    </div>
</div>
