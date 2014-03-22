{% import 'macro/notify.tpl' as notifytpl %}
<script src="{{ system.admin_tpl }}/js/ffcms.js" type="text/javascript"></script>
<script type="text/javascript" src="{{ system.script_url }}/resource/ckeditor/ckeditor.js"></script>
<script src="{{ system.script_url }}/resource/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
    $(document).ready(
            function()
            {
                // prepare ckeditor
                CKEDITOR.disableAutoInline = true;
                $('.wysi').ckeditor();
                $('.form-horizontal').submit(function(){
                    window.onbeforeunload = null;
                });
            }
    );
    window.onbeforeunload = function (evt) {
        var message = "{{ language.page_not_saved }}";
        if (typeof evt == "undefined") {
            evt = window.event;
        }
        if (evt) {
            evt.returnValue = message;
        }
        return message;
    }
</script>
<h1>{{ language.news_add_modedit_title }}</h1>
<hr />
{% if notify.notitle %}
    {{ notifytpl.error(language.news_add_edit_notify_title_length) }}
{% endif %}
{% if notify.nocat %}
    {{ notifytpl.error(language.news_add_edit_notify_category_wrong) }}
{% endif %}
{% if notify.wrongway %}
    {{ notifytpl.error(language.news_add_edit_notify_pathway_null) }}
{% endif %}
{% if notify.notext %}
    {{ notifytpl.error(language.news_add_edit_notify_text_null) }}
{% endif %}
{% if notify.captcha_error %}
    {{ notifytpl.error(language.news_add_edit_page_captcha_error) }}
{% endif %}
{% if notify.success %}
    {{ notifytpl.success(language.news_add_edit_notify_success_save) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            <h2>{{ language.news_add_edit_page_pathway_title }}</h2>

            <div class="input-group">
                <input class="form-control" type="text" id="out" name="pathway" value="{{ news.pathway }}" onkeyup="return pathCallback();">
                <span class="input-group-addon">.html</span>
            </div>
            <span class="help-block">{{ language.news_add_edit_page_pathway_desc }}</span>
        </div>
        <div class="col-md-6">
            <h2>{{ language.news_add_edit_page_date_text }}<small><input type="checkbox" id="setcurrentdate" name="current_date"/> {{ language.news_add_edit_page_current_date }}</small></h2>
            <input type="text" name="date" id="datefield" value="{{ news.date }}" class="form-control" />
            <span class="help-block">{{ language.news_add_edit_page_date_desc }}</span>
        </div>
    </div>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in system.languages %}
                <li{% if itemlang == system.lang %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
            {% endfor %}
        </ul>
        <div class="tab-content">
            {% for itemlang in system.languages %}
                <div class="tab-pane fade{% if itemlang == system.lang %} in active{% endif %}" id="{{ itemlang }}">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>{{ language.news_add_edit_page_title_name }}[{{ itemlang }}]</h2>
                            <input onkeyup="oJS.strNormalize(this)" type="text" name="title[{{ itemlang }}]" class="form-control" value="{{ news.title[itemlang] }}"/>
                            <span class="help-block">{{ language.news_add_edit_page_title_desc }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>{{ language.news_add_edit_page_textarea_title }}[{{ itemlang }}]</h2>
                            <textarea name="text[{{ itemlang }}]" id="textobject{{ itemlang }}" class="wysi form-control">{{ news.text[itemlang] }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h2>{{ language.news_add_edit_page_description }}[{{ itemlang }}]</h2>
                            <input type="text" name="description[{{ itemlang }}]" class="form-control" value="{{ news.description[itemlang] }}"/>
                            <span class="help-block">{{ language.news_add_edit_page_description_desc }}</span>
                        </div>
                        <div class="col-md-6">
                            <h2>{{ language.news_add_edit_page_keywords }}[{{ itemlang }}]</h2>
                            <input type="text" id="keywords[{{ itemlang }}]" name="keywords[{{ itemlang }}]" class="form-control" value="{{ news.keywords[itemlang] }}"/>
                            <input class="btn btn-info pull-right" type="button" value="{{ language.news_add_edit_page_keybutton_gen }}" onClick="countKeywords('{{ itemlang }}')">
                            <span class="help-block">{{ language.news_add_edit_page_keywords_description }}</span>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h2 id="postertitle">{{ language.news_add_edit_page_poster_title }}</h2>
            {% if news.poster_path %}
                <p class="alert alert-success" id="posterobject"><i class="fa fa-picture-o"></i> {{ news.poster_name }}
                    <a href="#postertitle" data-toggle="modal" data-target="#posterview" class="label label-info" target="_blank">{{ language.news_add_edit_page_poster_view }}</a>
                    <a href="#postertitle" onclick="return posterDelete({{ news.id }});" class="label label-danger">{{ language.news_add_edit_page_poster_del }}</a></p>
            {% endif %}
            <input type="file" name="newsimage">
            <span class="help-block">{{ language.news_add_edit_page_poster_desc }}</span>
        </div>
        <div class="col-md-6">
            <h2>{{ language.news_add_edit_page_category_name }}</h2>

            <div>
                <select name="category" size="5" class="form-control">
                    {% for cat in news.categorys %}
                        <option value="{{ cat.id }}"{% if cat.id == news.cat_id %} selected{% endif %}>{{ cat.name }}</option>
                    {% endfor %}
                </select>
            </div>
            <span class="help-block">{{ language.news_add_edit_page_category_desc }}</span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            {% if cfg.captcha_full %}
                <script>
                    var RecaptchaOptions = { theme : 'white' };
                </script>
                <h2>{{ language.news_add_edit_page_captcha_title }}</h2>
                {{ captcha }}
            {% else %}
                <h2>{{ language.news_add_edit_page_captcha_title }}</h2>
                <img src="{{ captcha }}" id="captcha"/><a href="#captcha" onclick="document.getElementById('captcha').src='{{ captcha }}?'+Math.random();"><i class="fa fa-refresh"></i></a><br/>
                <input type="text" name="captcha" class="form-control" required>
            {% endif %}
        </div>
    </div>
    <br />
    <div class="row">
        <div class="col-md-12">
            <input type="submit" name="save" value="{{ language.news_add_edit_page_button_save }}" class="btn btn-success btn-block"/>
        </div>
    </div>
</form>
{% if news.poster_path %}
    <div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" id="posterview" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ language.news_add_edit_page_image_preview }}</h4>
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