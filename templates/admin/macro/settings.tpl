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