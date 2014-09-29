{% import 'macro/notify.tpl' as notifytpl %}
<link href="{{ system.theme }}/css/datepicker.css" rel="stylesheet">
<script type="text/javascript" src="{{ system.theme }}/js/bootstrap-datepicker.js"></script>
<script src="{{ system.theme }}/js/maxlength.js"></script>
<script type="text/javascript" src="{{ system.script_url }}/resource/ckeditor/ckeditor.js"></script>
<script src="{{ system.script_url }}/resource/ckeditor/adapters/jquery.js"></script>
<script src="{{ system.theme }}/js/yandex-translate.js"></script>
<script type="text/javascript">
    $(document).ready(
            function()
            {
                CKEDITOR.disableAutoInline = true;
                $('.wysi').ckeditor({language: '{{ system.lang }}'});
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
<h1>{{ extension.title }}<small>{{ language.admin_component_static_edit }}</small></h1>
<hr />
{% include 'components/static/menu_include.tpl' %}
{% if notify.notitle %}
    {{ notifytpl.error(language.admin_component_static_page_titlenull) }}
{% endif %}
{% if notify.pathmatch %}
    {{ notifytpl.error(language.admin_component_static_page_pathused) }}
{% endif %}
{% if notify.success %}
    {{ notifytpl.success(language.admin_component_static_page_saved) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_component_static_edit_page_pathway }}</h2>
            <div class="input-group">
                <span class="input-group-addon"><a href="{{ system.url }}/static/{{ static.pathway }}.html" target="_blank"><i class="fa fa-share-square-o"></i></a></span>
                <span class="input-group-addon">{{ system.url }}/static/</span>
                <input type="text" id="out" name="pathway" value="{{ static.pathway }}" onkeyup="return pathCallback();" class="form-control" />
                <span class="input-group-addon">.html</span>
            </div>
            <span class="help-block">{{ language.admin_component_static_edit_page_pathway_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>{{ language.admin_component_static_edit_page_date_text }}<small><input type="checkbox" id="setcurrentdate" name="current_date"/> {{ language.admin_component_static_edit_page_current_date }}</small></h2>
            <input type="text" name="date" id="datefield" data-date-format="dd.mm.yyyy" value="{{ static.date }}" class="form-control"/>
            <span class="help-block">{{ language.admin_component_static_edit_page_date_desc }}</span>
        </div>
    </div>
    <p class="alert alert-info">{{ language.admin_component_static_language_alert }}</p>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in langs.all %}
                <li{% if itemlang == langs.current %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
                {% if itemlang != langs.current %}
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a onclick="return translateStatic('{{ langs.current }}', '{{ itemlang }}', '{{ system.yandex_translate_key }}');">{{ language.admin_autotranslate }} <span class="label label-danger">{{ langs.current }}</span> -> <span class="label label-success">{{ itemlang }}</span></a></li>
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
                        <h2>{{ language.admin_component_static_edit_page_title }}[{{ itemlang }}]</h2>
                        <input onkeyup="oJS.strNormalize(this)" type="text" name="title[{{ itemlang }}]" class="form-control" value="{{ static.title[itemlang] }}" maxlength="150" id="static_title_{{ itemlang }}" />
                        <span class="help-block">{{ language.admin_component_static_edit_page_title_desc }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ language.admin_component_static_edit_page_textarea_title }}[{{ itemlang }}]</h2>
                        <textarea name="text[{{ itemlang }}]" id="textobject{{ itemlang }}" class="wysi form-control">{{ static.text[itemlang] }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <h2>{{ language.admin_component_static_edit_page_description }}[{{ itemlang }}]</h2>
                        <input type="text" name="description[{{ itemlang }}]" class="form-control" value="{{ static.description[itemlang] }}" maxlength="250" id="static_desc_{{ itemlang }}" />
                        <span class="help-block">{{ language.admin_component_static_edit_page_description_desc }}</span>
                    </div>
                    <div class="col-lg-6">
                        <h2>{{ language.admin_component_static_edit_page_keywords }}[{{ itemlang }}]</h2>
                        <input type="text" id="keywords_{{ itemlang }}" name="keywords[{{ itemlang }}]" class="form-control" value="{{ static.keywords[itemlang] }}" maxlength="200" />
                        <input class="btn btn-info pull-right" type="button" value="{{ language.admin_component_static_edit_page_keybutton_gen }}" onClick="countKeywords('{{ itemlang }}')">
                        <span class="help-block">{{ language.admin_component_static_edit_page_keywords_description }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <div class="row">
        <div class="span4">
            <input type="submit" name="save" value="{{ language.admin_component_static_edit_page_button_save }}" class="btn btn-success btn-large"/>
        </div>
    </div>
</form>