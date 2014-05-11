{% macro textgroup(name, value, label, helper) %}
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ label }}</label>

        <div class="col-lg-9">
            <input class="form-control" type="text" name="cfg:{{ name }}" value="{{ value }}"/>
            <p class="help-block">{{ helper }}</p>
        </div>
    </div>
{% endmacro %}
{% macro selectYNgroup(name,selected,label,helper,global) %}
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ label }}</label>

        <div class="col-lg-9">
            <select name="cfg:{{ name }}" class="form-control">
                <option value="0"{% if selected == 0 %} selected{% endif %}>{{ global.language.admin_settings_isoff }}</option>
                <option value="1"{% if selected == 1 %} selected{% endif %}>{{ global.language.admin_settings_ison }}</option>
            </select>
            <p class="help-block">{{ helper }}</p>
        </div>
    </div>
{% endmacro %}
{% macro languagegroup(name,value,label,helper,global) %}
    <ul class="nav nav-tabs">
        {% for itemlang in global.system.languages %}
            <li{% if itemlang == global.system.lang %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ global.language.language }}: {{ itemlang|upper }}</a></li>
        {% endfor %}
    </ul>
    <br />
    <div class="tab-content">
        {% for itemlang in global.system.languages %}
            <div class="tab-pane fade{% if itemlang == global.system.lang %} in active{% endif %}" id="{{ itemlang }}">
                <div class="form-group">
                    <label class="col-lg-3 control-label">{{ label }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <input class="form-control" type="text"  name="cfg:{{ name }}[{{ itemlang }}]" value="{{ value[itemlang] }}"/>
                        <p class="help-block">{{ helper }}</p>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>
{% endmacro %}