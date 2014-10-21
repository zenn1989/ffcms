{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_menu_add_title }}</small></h1>
<hr />
{% if modmenu.menu_array|length < 1 %}
    {{ notifytpl.error(language.admin_modules_menu_add_notify_notpl) }}
{% endif %}
{% if notify.tag_wrong %}
    {{ notifytpl.error(language.admin_modules_menu_add_notify_sysname) }}
{% endif %}
{% if notify.tpl_wrong %}
    {{ notifytpl.error(language.admin_modules_menu_add_notify_tplwrong) }}
{% endif %}
{% if notify.name_wrong %}
    {{ notifytpl.error(language.admin_modules_menu_add_notify_nameempty) }}
{% endif %}
<form class="form-horizontal" role="form" method="post">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_modules_menu_add_sysname_title }}</label>
        <div class="col-lg-9">
            <input name="menu_tag" type="text" class="form-control" value="{{ modmenu.menu_tag }}" required="required">
            <span class="help-block">{{ language.admin_modules_menu_add_sysname_desc }}</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_modules_menu_add_tpl_title }}</label>
        <div class="col-lg-9">
            <select name="menu_tpl" class="form-control">
                {% for item in modmenu.menu_array %}
                    <option value="{{ item }}"{% if item == modmenu.menu_tpl %} selected{% endif %}>{{ item }}</option>
                {% endfor %}
            </select>
            <span class="help-block">{{ language.admin_modules_menu_add_tpl_desc }}</span>
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
                        <label class="control-label col-lg-3">{{ language.admin_modules_menu_add_name_title }}[{{ itemlang }}]</label>

                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="menu_name[{{ itemlang }}]" value="{{ modmenu.menu_title[itemlang] }}" maxlength="100"{% if itemlang == system.lang %} required="required" {% endif %}>
                            <span class="help-block">{{ language.admin_modules_menu_add_name_desc }}</span>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>
    </div>
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            <input type="checkbox" name="menu_display" id="menudisplay" {% if modmenu.menu_display == 1 %}checked {% endif %}/> <label for="menudisplay">{{ language.admin_modules_menu_add_display_label }}</label>
        </div>
    </div>
    <input type="submit" name="submit" class="btn btn-success" value="{{ language.admin_modules_menu_add_btn_save }}" />
    <a href="?object=modules&action=menu" class="btn btn-default">{{ language.admin_modules_menu_add_btn_cancel }}</a>
</form>