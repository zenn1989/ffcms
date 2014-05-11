<html lang="en">
<head>
    <title>{{ local.title }}</title>
    <link rel="canonical" href="{{ local.pathway }}" />
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body onload="window.print();">
<h1>{{ local.title }}</h1>
<div style="text-align: right">
    {% if local.show_date %}
        {{ local.date }} |
    {% endif %}
    URL: {{ local.pathway }}
</div>
<hr />
{{ local.text }}
</body>
</html>