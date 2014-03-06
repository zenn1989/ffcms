<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>FFCMS Install</title>
    <link href="{{ system.theme }}/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ system.theme }}/css/custom.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ system.script_url }}/resource/flags/flags.css" />
    <script src="{{ system.theme }}/js/jquery.min.js"></script>
    <script src="{{ system.theme }}/js/bootstrap.min.js"></script>
</head>

<body>

<div class="container">
    <div class="header">
        <ul class="nav nav-pills pull-right">
            <li><a href="?">{{ language.install_mainpage }}</a></li>
            <li><a href="?action=install">{{ language.install_mainahref }}</a></li>
            <li><a href="?action=update">{{ language.install_updatehref }}</a></li>
        </ul>
        <h3 class="text-muted">FFCMS Engine {{ system.version }}</h3>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    {{ language.install_switch_header }}
                    <div class="pull-right">
                        {% for lang_item in system.lang_available %}
                            <a href="?action=changelanguage&to={{ lang_item }}"><img class="flag flag-{{ lang_item }}" src="{{ system.script_url }}/resource/flags/blank.gif" /></a>
                        {% endfor %}
                    </div>
                </div>
                <div class="panel-body">
                    {{ content.body }}
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; <a href="http://ffcms.ru" target="_blank">FFCMS</a> content management system.</p>
    </div>

</div>

</body>
</html>
