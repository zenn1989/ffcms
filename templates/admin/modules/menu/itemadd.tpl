{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_menu_itemadd_title }}</small></h1>
<hr />
{% if notify.name_empty %}
    {{ notifytpl.error(language.admin_modules_menu_itemadd_notify_nameempty) }}
{% endif %}
{% if notify.url_wrong %}
    {{ notifytpl.error(language.admin_modules_menu_itemadd_notify_urlempty) }}
{% endif %}
<form class="form-horizontal" role="form" method="post">
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_modules_menu_itemadd_owner_title }}</label>
        <div class="col-lg-9">
            <input type="text" class="form-control" value="{{ modmenu.data.name }}[{{ modmenu.data.tag }}]" disabled>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_modules_menu_itemadd_elemowner_title }}</label>
        <div class="col-lg-9">
            <select name="menu_owner" class="form-control" {% if modmenu.have_chield %} disabled="disabled"{% endif %}>
                <option value="0">{{ language.admin_modules_menu_itemadd_elemowner_no }}</option>
                {% for owner in modmenu.elements %}
                <option value="{{ owner.id }}"{% if modmenu.data.owner_item == owner.id %} selected{% endif %}>{{ owner.name }}({{ owner.url }})</option>
                {% endfor %}
            </select>
            {% if modmenu.have_chield %}<span class="help-block">{{ language.admin_modules_menu_itemadd_chield_notify }}</span>{% endif %}
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_modules_menu_itemadd_element_url_title }}</label>
        <div class="col-lg-9">
            <input name="menu_url" type="text" class="form-control" required="required" value="{{ modmenu.general.url }}">
            <span class="help-block">{{ language.admin_modules_menu_itemadd_element_url_desc }}</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_modules_menu_itemadd_priority_title }}</label>
        <div class="col-lg-9">
            <input name="menu_priority" type="text" class="form-control" value="{{ modmenu.general.priority|default('0') }}">
            <span class="help-block">{{ language.admin_modules_menu_itemadd_priority_desc }}</span>
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
                        <label class="control-label col-lg-3">{{ language.admin_modules_menu_itemadd_elemname_title }}[{{ itemlang }}]</label>

                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="menu_name[{{ itemlang }}]" value="{{ modmenu.general.name[itemlang] }}" maxlength="100" {% if itemlang == system.lang %}required="required" {% endif %}>
                            <span class="help-block">{{ language.admin_modules_menu_itemadd_elemname_desc }}</span>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>
    </div>
    <input type="submit" name="submit" class="btn btn-success" value="{{ language.admin_modules_menu_itemadd_btn_save }}" />
    <a href="?object=modules&action=menu&make=manage&id={{ modmenu.data.id }}" class="btn btn-default">{{ language.admin_modules_menu_itemadd_btn_cancel }}</a>
</form>