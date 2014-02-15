{% macro error(text) %}
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>{{ text }}
    </div>
{% endmacro %}
{% macro success(text) %}
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert">×</button>{{ text }}
    </div>
{% endmacro %}
{% macro warning(text) %}
    <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert">×</button>{{ text }}
    </div>
{% endmacro %}