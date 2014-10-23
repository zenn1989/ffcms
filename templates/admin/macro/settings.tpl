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
        <label class="col-lg-3 control-label" for="macro-{{ name }}">{{ label }}</label>

        <div class="col-lg-9">
            <input type="hidden" name="cfg:{{ name }}" value="0" />
            <input id="macro-{{ name }}" type="checkbox" name="cfg:{{ name }}" class="switchable" value="1" {% if selected == 1 %}checked {% endif %} />
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