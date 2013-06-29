<script type="text/javascript" charset="utf-8">
    $().ready(function () {
        var elf = $('#elfinder').elfinder({
            // lang: 'ru',             // language (OPTIONAL)
            url: '{$url}/api.php?action=adminfiles'  // connector URL (REQUIRED)
        }).elfinder('instance');
    });
</script>
<div id="elfinder"></div>
<p>{$lang::admin_filemanager_desc}</p>