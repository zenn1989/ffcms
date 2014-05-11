<html lang="en">
<head>
    <title>{{ local.title }}</title>
    <link rel="canonical" href="{{ system.nolang_url }}{{ local.pathway }}" />
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body onload="window.print();">
<h1>{{ local.title }}</h1>
<div style="text-align: right">
    {{ local.date }} |
    URL: {{ system.nolang_url }}{{ local.pathway }}
</div>
<hr />
{{ local.text }}
</body>
</html>