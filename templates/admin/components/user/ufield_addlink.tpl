{% import 'macro/formdecorator.tpl' as formdecorator %}
{{ formdecorator.switch(system.script_url) }}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_ufields_addedit_title }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
{% if notify.smallname %}
    {{ notifytpl.error(language.admin_component_usercontrol_ufields_addedit_text_notify_wrongname) }}
{% endif %}
{% if notify.wrong_domain %}
    {{ notifytpl.error(language.admin_component_usercontrol_ufields_addedit_link_notify_domain) }}
{% endif %}
<form class="form-horizontal" role="form" method="post">
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_component_usercontrol_ufields_addedit_type_title }}</label>
        <div class="col-lg-9">
            <input name="field_type" type="text" class="form-control" value="text" disabled>
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
                    <div class="form-group">
                        <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_ufields_addedit_name_title }}[{{ itemlang }}]</label>

                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="field_name[{{ itemlang }}]" value="{{ ufields.name[itemlang] }}" maxlength="100" {% if itemlang == system.lang %}required="required" {% endif %}>
                            <span class="help-block">{{ language.admin_component_usercontrol_ufields_addedit_name_desc }}</span>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_component_usercontrol_ufields_addedit_link_domain_title }}</label>
        <div class="col-lg-9">
            <input name="field_linkdomain" type="text" class="form-control" required="required" value="{{ ufields.linkdomain }}">
            <span class="help-block">{{ language.admin_component_usercontrol_ufields_addedit_link_domain_desc }}</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label" for="field_linkredirect">{{ language.admin_component_usercontrol_ufields_addedit_link_redirect_title }}</label>
        <div class="col-lg-9">
            <input type="hidden" name="field_linkredirect" value="0" />
            <input id="field_linkredirect" type="checkbox" name="field_linkredirect" class="switchable" value="1"{% if ufields.linkredirect == 1 %} checked="checked" {% endif %} />
            <span class="help-block">{{ language.admin_component_usercontrol_ufields_addedit_link_redirect_desc }}</span>
        </div>
    </div>
    <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success" />
</form>