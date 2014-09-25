<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ system.script_url }}/resource/bootstrap/3.2.0/css/bootstrap.min.css">

    <script>
        var CKcallback = {{ ckcallback }};
        function callCkeditor(url) {
            window.opener.CKEDITOR.tools.callFunction(CKcallback, url);
            window.close();
        }
    </script>
</head>
<body>
<h2 class="text-center">{{ language.manager_filebrowser_title }}</h2>
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs nav-justified">
            <li{% if file_type == 1 %} class="active"{% endif %}><a href="{{ system.script_url }}/api.php?iface=back&object=ckbrowser&type=1&CKEditorFuncNum={{ ckcallback }}">{{ language.manager_filebrowser_tab_image }}</a></li>
            <li{% if file_type == 2 %} class="active"{% endif %}><a href="{{ system.script_url }}/api.php?iface=back&object=ckbrowser&type=2&CKEditorFuncNum={{ ckcallback }}">{{ language.manager_filebrowser_tab_flash }}</a></li>
            <li{% if file_type == 0 %} class="active"{% endif %}><a href="{{ system.script_url }}/api.php?iface=back&object=ckbrowser&type=0&CKEditorFuncNum={{ ckcallback }}">{{ language.manager_filebrowser_tab_other }}</a></li>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>{{ language.manager_filebrowser_table_head_preview }}</th>
                        <th>{{ language.manager_filebrowser_table_head_name }}</th>
                        <th>{{ language.manager_filebrowser_table_head_selector }}</th>
                    </tr>
                </thead>
                <tbody>
                {% for item in files %}
                <tr>
                    <td>{{ loop.index }}</td>
                    <td>
                        {% if file_type == 1 %}
                        <img class="img-responsive img-thumbnail" src="{{ system.script_url }}/{{ item.path }}" style="max-height: 60px;">
                        {% elseif file_type == 2 %}
                        <img class="img-responsive img-thumbnail" src="{{ system.script_url }}/resource/cmscontent/swf_bar.jpg" style="max-height: 60px;">
                        {% else %}
                        <img class="img-responsive img-thumbnail" src="{{ system.script_url }}/resource/cmscontent/file_bar.jpg" style="max-height: 60px;">
                        {% endif %}
                    </td>
                    <td><h3>{{ item.name }} <small>{{ system.script_url }}/{{ item.path }}</small></h3></td>
                    <td><a href="#" class="btn btn-success btn-block" onclick="return callCkeditor('{{ system.script_url }}/{{ item.path }}')">{{ language.manager_filebrowser_file_select }}</a></td>
                </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>