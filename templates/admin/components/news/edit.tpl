{% import 'macro/notify.tpl' as notifytpl %}
<link href="{{ system.theme }}/css/datepicker.css" rel="stylesheet">
<script type="text/javascript" src="{{ system.theme }}/js/bootstrap-datepicker.js"></script>
<script src="{{ system.theme }}/js/maxlength.js"></script>
<link rel="stylesheet" href="{{ system.theme }}/css/fileupload/jquery.fileupload.css">
<script type="text/javascript" src="{{ system.script_url }}/resource/ckeditor/ckeditor.js"></script>
<script src="{{ system.script_url }}/resource/ckeditor/adapters/jquery.js"></script>
<script src="{{ system.theme }}/js/yandex-translate.js"></script>
<script type="text/javascript">
    $(document).ready(
            function()
            {
                // prepare ckeditor
                CKEDITOR.disableAutoInline = true;
                $('.wysi').ckeditor({language: '{{ system.lang }}'});
                // prepare jquery image uploader
                $.getJSON("/api.php?iface=back&object=jqueryfile&action=list&id={{ news.id }}",
                        function (data) {
                            $.each(data.files, function (index, file) {
                                $('<p/>').html(
                                        '<p class="alert alert-success" id="'+file.name+'">'+file.name +
                                                ' <a href="'+file.url+'" class="label label-info" target="_blank">{{ language.admin_component_news_edit_page_poster_view }}</a>'+
                                                ' <a href="#gallerytitle" class="label label-danger" onclick="return gallerydel(\''+file.name+'\', {{ news.id }});">{{ language.admin_component_news_edit_page_poster_del }}</a>' +
                                        '</p>'
                                ).appendTo('#files');
                            });
                        });
                $('.form-horizontal').submit(function() {
                    var is_fail = false;
                    $.ajax({
                        async: false,
                        type: 'GET',
                        url: ffcms_host + '/api.php?iface='+loader+'&object=checkauth',
                        success: function(data) {
                            if(data < 1) {
                                is_fail = true;
                            }
                        },
                        error: function() {
                            is_fail = true;
                        }
                    });
                    if(is_fail) {
                        if(!confirm('{{ language.admin_formsubmit_notify }}'))
                            return false;
                    }
                    window.onbeforeunload = null;
                });
                $('#datefield').datepicker();
                $('input[maxlength]').maxlength({alwaysShow: true});
            }
    );
    window.onbeforeunload = function (evt) {
        var message = "{{ language.admin_page_not_saved }}";
        if (typeof evt == "undefined") {
            evt = window.event;
        }
        if (evt) {
            evt.returnValue = message;
        }
        return message;
    }
</script>
<script src="{{ system.theme }}/js/fileupload/vendor/jquery.ui.widget.js"></script>
<script src="{{ system.theme }}/js/fileupload/jquery.iframe-transport.js"></script>
<script src="{{ system.theme }}/js/fileupload/jquery.fileupload.js"></script>
<h1>{{ extension.title }}<small>{{ language.admin_component_news_modedit_title }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
{% if notify.notitle %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_title_length) }}
{% endif %}
{% if notify.nocat %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_category_wrong) }}
{% endif %}
{% if notify.wrongway %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_pathway_null) }}
{% endif %}
{% if notify.notext %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_text_null) }}
{% endif %}
{% if notify.success %}
    {{ notifytpl.success(language.admin_component_news_edit_notify_success_save) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
    {% if news.action_add %}
    <input type="hidden" name="news_gallery_id" value="{{ news.id }}" />
    {% endif %}
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_pathway_title }}</h2>

            <div class="input-group">
                <input class="form-control" type="text" id="out" name="pathway" value="{{ news.pathway }}" onkeyup="return pathCallback();">
                <span class="input-group-addon">.html</span>
            </div>
            <span class="help-block">{{ language.admin_component_news_edit_page_pathway_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_date_text }}<small><input type="checkbox" id="setcurrentdate" name="current_date"/> {{ language.admin_component_news_edit_page_current_date }}</small></h2>
            <input type="text" name="date" id="datefield" data-date-format="dd.mm.yyyy" value="{{ news.date }}" class="form-control" />
            <span class="help-block">{{ language.admin_component_news_edit_page_date_desc }}</span>
        </div>
    </div>
    <p class="alert alert-info">{{ language.admin_component_news_alert_info }}</p>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in langs.all %}
                <li{% if itemlang == langs.current %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
                {% if itemlang != langs.current %}
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" onclick="return translateNews('{{ langs.current }}', '{{ itemlang }}', '{{ system.yandex_translate_key }}');">{{ language.admin_autotranslate }} <span class="label label-danger">{{ langs.current }}</span> -> <span class="label label-success">{{ itemlang }}</span></a></li>
                        </ul>
                    </li>
                {% endif %}
            {% endfor %}
        </ul>
        <div class="tab-content">
            {% for itemlang in langs.all %}
            <div class="tab-pane fade{% if itemlang == langs.current %} in active{% endif %}" id="{{ itemlang }}">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ language.admin_component_news_edit_page_title_name }}[{{ itemlang }}]</h2>
                        <input{% if itemlang == langs.current %} onkeyup="oJS.strNormalize(this)"{% endif %} type="text" name="title[{{ itemlang }}]" class="form-control" value="{{ news.title[itemlang] }}" maxlength="100" id="news_title_{{ itemlang }}" />
                        <span class="help-block">{{ language.admin_component_news_edit_page_title_desc }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ language.admin_component_news_edit_page_textarea_title }}[{{ itemlang }}]</h2>
                        <textarea name="text[{{ itemlang }}]" id="textobject{{ itemlang }}" class="wysi form-control">{{ news.text[itemlang] }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <h2>{{ language.admin_component_news_edit_page_description }}[{{ itemlang }}]</h2>
                        <input type="text" name="description[{{ itemlang }}]" class="form-control" value="{{ news.description[itemlang] }}" maxlength="250" id="news_desc_{{ itemlang }}" />
                        <span class="help-block">{{ language.admin_component_news_edit_page_description_desc }}</span>
                    </div>
                    <div class="col-lg-6">
                        <h2>{{ language.admin_component_news_edit_page_keywords }}[{{ itemlang }}]</h2>
                        <input type="text" id="keywords_{{ itemlang }}" name="keywords[{{ itemlang }}]" class="form-control" value="{{ news.keywords[itemlang] }}" maxlength="200" />
                        <input class="btn btn-info pull-right" type="button" value="{{ language.admin_component_news_edit_page_keybutton_gen }}" onClick="countKeywords('{{ itemlang }}')">
                        <span class="help-block">{{ language.admin_component_news_edit_page_keywords_description }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <h2 id="postertitle">{{ language.admin_component_news_edit_page_poster_title }}</h2>
            {% if news.poster_path %}
                <p class="alert alert-success" id="posterobject"><i class="fa fa-picture-o"></i> {{ news.poster_name }}
                    <a href="#postertitle" data-toggle="modal" data-target="#posterview" class="label label-info" target="_blank">{{ language.admin_component_news_edit_page_poster_view }}</a>
                    <a href="#postertitle" onclick="return posterDelete({{ news.id }});" class="label label-danger">{{ language.admin_component_news_edit_page_poster_del }}</a></p>
            {% endif %}
            <input type="file" name="newsimage">
            <span class="help-block">{{ language.admin_component_news_edit_page_poster_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2 id="gallerytitle">{{ language.admin_component_news_edit_page_mediagallery_title }}</h2>
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>{{ language.admin_component_news_edit_page_mediagallery_addbutton }}</span>
                <!-- The file input field used as target for the file upload widget -->
                <input id="fileupload" type="file" name="files">
            </span>
            <br>
            <br>
            <!-- The global progress bar -->
            <div id="progress" class="progress">
                <div class="progress-bar progress-bar-success"></div>
            </div>
            <!-- The container for the uploaded files -->
            <div id="files" class="files"></div>
            <span class="help-block">{{ language.admin_component_news_edit_page_mediagallery_desc }}</span>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_category_name }}</h2>

            <div>
                <select name="category" size="5" class="form-control">
                    {% for cat in news.categorys %}
                        <option value="{{ cat.id }}"{% if cat.id == news.cat_id %} selected{% endif %}>{{ cat.name }}</option>
                    {% endfor %}
                </select>
            </div>
            <span class="help-block">{{ language.admin_component_news_edit_page_category_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_params_name }}</h2>
            <label class="checkbox">
                <input type="checkbox" name="display_content"{% if news.display == 1 %} checked{% endif %} /> {{ language.admin_component_news_edit_page_display_ckechbox }}
            </label>
            <label class="checkbox">
                <input type="checkbox" name="important_content"{% if news.important == 1 %} checked{% endif %} /> {{ language.admin_component_news_edit_page_important_ckechbox }}
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <input type="submit" name="save" value="{{ language.admin_component_news_edit_page_button_save }}" class="btn btn-success btn-large"/>
        </div>
    </div>
</form>
{% if news.poster_path %}
    <div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" id="posterview" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ language.admin_component_news_edit_page_image_preview }}</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <!-- no browser cache :D -->
                        <script>
                            document.write('<img src="{{ system.script_url }}{{ news.poster_path }}?rnd='+Math.random()+'"/>');
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endif %}
<script>
    $(function () {
        'use strict';
        var url = '/api.php?iface=back&object=jqueryfile&action=upload&id={{ news.id }}';
        $('#fileupload').fileupload({
            url: url,
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('<p/>').html(
                            '<p class="alert alert-success" id="'+file.name+'">'+file.name +
                                    ' <a href="'+file.url+'" class="label label-info" target="_blank">{{ language.admin_component_news_edit_page_poster_view }}</a>'+
                                    ' <a href="#gallerytitle" class="label label-danger" onclick="return gallerydel(\''+file.name+'\', {{ news.id }});">{{ language.admin_component_news_edit_page_poster_del }}</a>' +
                                    '</p>'
                    ).appendTo('#files');
                });
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                );
            }
        }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });
</script>