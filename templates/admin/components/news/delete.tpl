<h1>{{ extension.title }}<small>{{ language.admin_component_news_delete }}</small></h1>
<hr />

<p>{{ language.admin_component_news_delete_warning }}</p>
<table class="table table-bordered table-responsive">
    <thead>
    <tr>
        <th>{{ language.admin_component_news_delete_th1 }}</th>
        <th>{{ language.admin_component_news_delete_th2 }}</th>
        <th>{{ language.admin_component_news_delete_th3 }}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ news.id }}</td>
        <td>{{ news.title }}</td>
        <td>{{ news.pathway }}</td>
    </tr>
    </tbody>
</table>
<form method="post" action="">
    <input type="submit" name="submit" value="{{ language.admin_component_news_delete_button_success }}" class="btn btn-danger"/>
    <a href="?object=components&action=news" class="btn btn-success">{{ language.admin_component_news_delete_button_cancel }}</a>
</form>