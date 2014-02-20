<h1>{{ extension.title }}<small>{{ language.admin_component_feedback_list }}</small></h1>
<hr />
{% include 'components/feedback/menu_include.tpl' %}
<table class="table table-responsive table-bordered">
    <thead>
    <tr>
        <th>{{ language.admin_component_feedback_th1 }}</th>
        <th>{{ language.admin_component_feedback_th2 }}</th>
        <th>{{ language.admin_component_feedback_th3 }}</th>
        <th>{{ language.admin_component_feedback_th4 }}</th>
        <th>{{ language.admin_component_feedback_th5 }}</th>
    </tr>
    </thead>
    <tbody>
    {% for row in feedback.result %}
        <tr>
            <td>{{ row.id }}</td>
            <td>{{ row.from_name }}({{ row.from_email }})</td>
            <td>{{ row.title|escape|striptags }}</td>
            <td>{{ row.time|date('d.m.Y H:i') }}</td>
            <td><a href="?object=components&action=feedback&make=read&id={{ row.id }}">{{ language.admin_component_feedback_readit }}</a></td>
        </tr>
    {% endfor %}
    </tbody>
</table>

{{ pagination }}