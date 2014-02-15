<link rel="stylesheet" href="{{ system.script_url }}/resource/elrte/css/smoothness/jquery-ui-1.8.13.custom.css" type="text/css" media="screen">
<link rel="stylesheet" type="text/css" media="screen" href="{{ system.script_url }}/resource/elfinder/css/elfinder.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="{{ system.script_url }}/resource/elfinder/css/theme.css">
<script src="{{ system.script_url }}/resource/elrte/js/jquery-ui.min.js" type="text/javascript"></script>
<script type="text/javascript" src="{{ system.script_url }}/resource/elfinder/js/elfinder.min.js"></script>
<script type="text/javascript" src="{{ system.script_url }}/resource/elfinder/js/i18n/elfinder.ru.js"></script>

<script type="text/javascript" charset="utf-8">
    $().ready(function () {
        var elf = $('#elfinder').elfinder({
            // lang: 'ru',             // language (OPTIONAL)
            url: '{{ system.script_url }}/api.php?iface=back&object=elfinder'  // connector URL (REQUIRED)
        }).elfinder('instance');
    });
</script>
<h1>{{ language.admin_nav_li_filemanager }}</h1>
<hr />
<p>{{ language.admin_filemanager_desc }}</p>
<div id="elfinder"></div>