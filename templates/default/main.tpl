<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{ meta.title }}</title>
    <meta name="keywords" content="{{  meta.keywords }}"/>
    <meta name="description" content="{{ meta.description }}"/>
    <meta name="generator" content="{{ meta.generator }}"/>
    <meta name="robots" content="all"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">
        var host = '{{ system.script_url }}';
        var loader = '{{ system.loader }}';
    </script>
    <link href="{{ system.script_url }}/resource/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ system.script_url }}/resource/fontawesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="{{ system.theme }}/css/custom.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ system.script_url }}/resource/flags/flags.css" />
    <link href="{{ system.script_url }}/favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <script src="{{ system.script_url }}/resource/jquery/1.11.1/jquery-1.11.1.min.js"></script>
    <script src="{{ system.script_url }}/resource/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script src="{{ system.theme }}/js/ffcms.js"></script>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

{% include 'widgets/head_menu.tpl' %}

<div class="container">

    <div class="row">
        <div class="col-md-9">
            <a href="{{ system.url }}"><img src="{{ system.theme }}/img/logo.png" /></a>
        </div>
        <div class="col-md-3">
            {% include 'widgets/search_form.tpl' %}
        </div>
    </div>

    <div class="row container-content">

        <div class="col-md-3">
            {{ module.menu.left }}
            {{ module.news_top_discus }}
            {{ module.news_top_view }}
            {{ module.news_new }}
            {{ module.lastcomments }}
            {{ module.tag_cloud }}
        </div>

        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-body">
                    {% if content.body == null %}
                        <h3>{{ language.global_404_title }}</h3>
                        <hr />
                        <p>{{ language.global_404_desc }}</p>
                    {% else %}
                        <div class="pull-right">
                            {% include 'widgets/bookmarks.tpl' %}
                        </div>
                        {{ content.body }}
                    {% endif %}
                </div>
            </div>
        </div>

    </div>

</div>

<div class="content-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                {% include 'widgets/footer.tpl' %}
            </div>
        </div>
    </div>
</div>
</body>
</html>
