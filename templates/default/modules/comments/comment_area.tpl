<script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/jquery.wysibb.js"></script>
{% if system.lang in ['ar', 'cn', 'de', 'en', 'fr', 'pl', 'tr', 'ua', 'vn', 'ru'] %}
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
        <div style="padding-top: 15px;"> <!-- padding for wisybb popover -->
            {% if user.id > 0 %}
                <div class="row">
                    <div class="col-md-12">
                        <textarea style="width: 95%;" placeHolder="{{ language.comments_text_writemsg }}" id="comment_value" class="wysibb-editor" rows="5"></textarea>
                        <div class="pull-right"><a href="#comment-add" id="comment_send" class="btn btn-success">{{ language.global_send_button }}</a></div>
                    </div>
                </div>
            {% elseif guest_access %}
                <div class="row">
                    <div class="col-md-12">
                        <textarea style="width: 95%;" placeHolder="{{ language.comments_text_writemsg }}" id="comment_value" class="wysibb-editor" rows="5"></textarea>
                        <div class="pull-right"><a href="#comment-guest" data-toggle="modal" class="btn btn-success">{{ language.global_send_button }}</a></div>
                    </div>
                </div>
            {% else %}
            <p class="alert alert-warning">{{ language.comments_register_msg }}</p>
            {% endif %}
        </div>
        <div id="comment_list" class="well-item">
            {{ comments }}
        </div>
        <br />
        <div class="row" id="comment_load">
            <div class="col-md-6">
                <a href="#comment_load" class="btn btn-default btn-block"
                   id="doLoadComment">{{ language.comments_text_loadmore }}</a>
            </div>
            <div class="col-md-6">
                <a href="#comment_load" class="btn btn-default btn-block"
                   id="doLoadAllComment">{{ language.comments_text_loadall }}</a>
            </div>
        </div>
    </div>
    <!--
    <div class="tab-pane" id="comment_vk">Code vk.com here</div>
    -->
</div>

<!-- Edit comment form pop-up modal. Id #comment-edit-jquery must be setted, used by jquery -->
<div id="edit-comment" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
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
    </div>
</div>

<!-- guest modal form -->
<div class="modal fade" id="comment-guest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ language.comment_api_guest_title }}</h4>
            </div>
            <div class="modal-body">
            <form class="form-horizontal" role="form">
                <div class="form-group">
                    <label class="control-label col-md-4">{{ language.comment_api_guest_name }}</label>
                    <div class="col-md-8"><input type="text" class="form-control" name="guest_name" maxlength="16" /></div>
                </div>
                {% if captcha.full %}
                    <script>
                        var RecaptchaOptions = { theme : 'white' };
                    </script>
                    {{ captcha.image }}
                {% else %}
                    <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-8">
                        <img src="{{ captcha.image }}" id="captcha"/>
                        <a href="#" onclick="document.getElementById('captcha').src='{{ captcha.image }}?'+Math.random();"><i class="fa fa-refresh"></i></a>
                        <input type="text" name="captcha" class="form-control" required>
                    </div>
                    </div>
                {% endif %}
            </form>
            </div>
            <div class="modal-footer">
                <a href="#comment-add" id="comment_send" class="btn btn-success">{{ language.global_send_button }}</a>
            </div>
        </div>
    </div>
</div>
