<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{ meta.title }} - {{ language.site_maintenance_title }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">
        var host = '{{ system.script_url }}';
        var loader = '{{ system.loader }}';
    </script>
    <link href="{{ system.script_url }}/resource/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ system.script_url }}/resource/fontawesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ system.script_url }}/resource/flags/flags.css" />
    <link href="{{ system.script_url }}/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <!-- DIE OLD IE !111one -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">

    <div class="row">
        <div class="col-md-12">
            <h1>{{ language.site_maintenance_h1 }}</h1>
            <hr />
            <p>{{ language.site_maintenance_desc }}</p>
            {% if user.id < 1 %}
                {{ login_form }}
            {% endif %}
        </div>
    </div>
</div>
</body>
</html>