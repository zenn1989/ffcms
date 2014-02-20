<h1>{{ extension.title }}<small>{{ language.admin_component_feedback_read }}</small></h1>
<hr />
{% include 'components/feedback/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-8">
        <p class="alert alert-info">
            <strong>{{ language.admin_component_feedback_readtitle }}</strong> : {{ feedback.result.title }}
        </p>
        <h2>{{ language.admin_component_feedback_readmess }}</h2>
        <pre>{{ feedback.result.text|escape|striptags }}</pre>
    </div>
    <div class="col-lg-4">
        <table class="table table-responsive table-bordered">
            <thead>
            <tr>
                <th>{{ language.admin_component_feedback_sender }}</th>
                <th>{{ language.admin_component_feedback_sendermail }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ feedback.result.from_name }}</td>
                <td><a href="mailto:{{ feedback.result.from_email }}?subject={{ feedback.result.title|url_encode(true) }}&body={{ feedback.result.text|url_encode(true) }}" target="_blank">{{ feedback.result.from_email }}</a></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>