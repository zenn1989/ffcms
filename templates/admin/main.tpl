<!doctype html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <title>{$lang::admin_page_title}</title>

    <meta charset="utf-8">

    <meta name="author" content="zenn">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="stylesheet" href="{$url}/{$tpl_dir}/css/font-awesome.css">

    <link rel="stylesheet" href="{$url}/{$tpl_dir}/css/bootstrap.css">
    <link rel="stylesheet" href="{$url}/{$tpl_dir}/css/bootstrap-responsive.css">

    <link rel="stylesheet" href="{$url}/{$tpl_dir}/css/application.css">

    <link rel="stylesheet" href="{$url}/resource/elrte/css/elrte.min.css" type="text/css" media="screen">
    <link rel="stylesheet" href="{$url}/resource/elrte/css/smoothness/jquery-ui-1.8.13.custom.css" type="text/css"
          media="screen">


    <link rel="stylesheet" type="text/css" media="screen" href="{$url}/resource/elfinder/css/elfinder.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="{$url}/resource/elfinder/css/theme.css">

    <script src="{$url}/{$tpl_dir}/js/jquery.min.js"></script>
    <script src="{$url}/resource/elrte/js/jquery-ui.min.js" type="text/javascript"></script>
    <script src="{$url}/{$tpl_dir}/js/bootstrap.min.js"></script>

    <script src="{$url}/resource/elrte/js/elrte.min.js" type="text/javascript"></script>
    <script src="{$url}/resource/elrte/js/i18n/elrte.ru.js" type="text/javascript"></script>
    <script type="text/javascript" src="{$url}/resource/elfinder/js/elfinder.min.js"></script>
    <script type="text/javascript" src="{$url}/resource/elfinder/js/i18n/elfinder.ru.js"></script>
</head>

<body>

<div id="wrapper">

    <div id="topbar">

        <div class="container">

            <div id="top-nav">

                <ul>
                    <li class="dropdown">
                        <a href="{$url}" target="_blank">{$lang::admin_open_site_link}</a>
                        <i class="icon-arrow-up"></i>
                    </li>
                </ul>

                <ul class="pull-right">
                    <li><i class="icon-user"></i> {$lang::admin_welcome_msg} {$username}</li>
                    <li><a href="{$url}/logout">{$lang::admin_exit_link}</a></li>
                </ul>

            </div>
            <!-- /#top-nav -->

        </div>
        <!-- /.container -->

    </div>
    <!-- /#topbar -->

    {$header}

    <div id="masthead">

        <div class="container">

            <div class="masthead-pad">

                <div class="masthead-text">
                    <h2>{$lang::admin_panel_header}</h2>

                    <p>{$lang::admin_panel_msg_welcome}</p>
                </div>
                <!-- /.masthead-text -->

            </div>

        </div>
        <!-- /.container -->

    </div>
    <!-- /#masthead -->


    <div id="content">
        {$body}
    </div>
    <!-- /#content -->

</div>
<!-- /#wrapper -->


<div id="footer">

    <div class="container">

        <div class="row">

            <div class="span6">
                © 2012 <a href="http://ffcms.ru">ffcms</a>, all rights reserved.
            </div>
            <!-- /span6 -->

        </div>
        <!-- /row -->

    </div>
    <!-- /container -->

</div>
<!-- /#footer -->
<script type="text/javascript" charset="utf-8">
    $().ready(function () {
        var opts = {
            cssClass: 'el-rte',
            lang: 'en',
            height: 350,
            fmOpen: function (callback) {
                $('<div/>').dialogelfinder({
                    url: '{$url}/api.php?&action=elfinder', // connector URL (REQUIRED)
                    // lang: 'ru', // elFinder language (OPTIONAL)
                    commandsOptions: {
                        getfile: {
                            oncomplete: 'destroy' // destroy elFinder after file selection
                        }
                    },
                    getFileCallback: callback // pass callback to editor
                });
            },
            cssfiles: ['{$url}/resource/elrte/css/elrte-inner.css']
        }
        $('.wysi').elrte(opts);
        if ($("[rel=tooltip]").length) {
            $("[rel=tooltip]").tooltip();
        }
    })

</script>
</body>
</html>