{% macro li_pagination(url, addlink, index, isactive) %}
    <li{% if isactive %} class="active"{% endif %}><a href="{{ url }}/{{ addlink }}/{% if index > 0 %}{{ index }}{% endif %}">{{ index+1 }}</a></li>
{% endmacro %}
{% macro li(text, class, url, addition_url) %}
    {% if url %}
        <li{% if class %} class="{{ class }}"{% endif %}><a href="{{ url }}/{% if addition_url %}{{ addition_url }}{% endif %}">{{ text }}</a></li>
    {% else %}
        <li{% if class %} class="{{ class }}"{% endif %}>{{ text }}</li>
    {% endif %}
{% endmacro %}