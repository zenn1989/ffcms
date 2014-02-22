<!DOCTYPE html>
<html lang="en">
<head>
    {% block header %}
        <script type="text/javascript">
            var host = '{{ system.script_url }}';
        </script>
        <meta charset="utf-8"/>
        <title>{{ meta.title }}</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
        <meta name="keywords" content="{{  meta.keywords }}"/>
        <meta name="description" content="{{ meta.description }}"/>
        <meta name="generator" content="{{ meta.generator }}"/>
        <meta name="robots" content="all"/>
        <link rel="stylesheet" href="{{ system.theme }}/css/bootstrap.css" />
        <link rel="stylesheet" href="{{ system.theme }}/css/bootstrap-responsive.css" />
        <link rel="stylesheet" href="{{ system.theme }}/css/docs.css" />
        <link rel="stylesheet" href="{{ system.theme }}/css/font-awesome.min.css" />
        <link rel="stylesheet" href="{{ system.script_url }}/resource/flags/flags.css" />
        <script type="text/javascript" src="{{ system.theme }}/js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="{{ system.theme }}/js/bootstrap.js"></script>
        <script type="text/javascript" src="{{ system.theme }}/js/pace.js"></script>
        <script type="text/javascript" src="{{ system.theme }}/js/ffcms.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    {% endblock %}
</head>
<body>
{% include 'widgets/head_menu.tpl' %}
<div class="container">
    <div class="row">
        <!-- Logotype -->
        <div class="span7">
            <a href="{{ system.url }}">
                <img src="{{ system.theme }}/img/logo.png"/>
            </a>
        </div>
        <!-- /Logotype -->
        <!-- search menu -->
        <div class="span5">
            <div class="pull-right">
                {% include 'widgets/search_form.tpl' %}
            </div>
        </div>
        <!-- /search menu -->
    </div>
    <div style="padding-top: 10px;"></div>
    <div class="row">
        <div class="span3" id="lnav">
            {% include 'widgets/link_menu.tpl' %}
            {{ module.tag_cloud }}
            {{ module.lastcomments }}
        </div>
        <div class="span9">
            <div class="well well-500px">
                <div class="pull-right">
                    {% include 'widgets/bookmarks.tpl' %}
                </div>
                <br />
                {% if content.body == null %}
                    <h3>{{ language.global_404_title }}</h3>
                    <hr />
                    <p>{{ language.global_404_desc }}</p>
                {% else %}
                    {{ content.body }}
                {% endif %}
            </div>
        </div>
    </div>
</div>

<hr class="soften"/>

{% include 'widgets/footer.tpl' %}
</body>
</html>
