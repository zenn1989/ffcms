<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ language.admin_page_title }}</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ system.theme }}/css/bootstrap.css" rel="stylesheet">

    <!-- Add custom CSS here -->
    <link href="{{ system.theme }}/css/sb-admin.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ system.theme }}/font-awesome/css/font-awesome.min.css">

    <script src="{{ system.theme }}/js/jquery-1.10.2.js"></script>
    <script src="{{ system.theme }}/js/bootstrap.js"></script>
    <script src="{{ system.theme }}/js/ffcms.js"></script>
</head>

<body>

<div id="wrapper">

    <!-- Sidebar -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ system.url }}/{{ system.file_name }}"><i class="fa fa-globe"></i> FFCMS ADMIN</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav side-nav">
                <li><a href="{{ system.url }}/{{ system.file_name }}"><i class="fa fa-home"></i> {{ language.admin_main_link }}</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-fire"></i> {{ language.admin_nav_system }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="?object=settings"><i class="fa fa-cogs"></i> {{ language.admin_nav_li_settings }}</a></li>
                        <li><a href="?object=filemanager"><i class="fa fa-file-o"></i> {{ language.admin_nav_li_filemanager }}</a></li>
                        <li><a href="?object=antivirus"><i class="fa fa-shield"></i> {{ language.admin_nav_li_avir }}</a></li>
                        <li><a href="?object=dump"><i class="fa fa-floppy-o"></i> {{ language.admin_nav_li_backup }}</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-table"></i> {{ language.admin_nav_modules }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        {% for module_data in content.modmenu.modules %}
                            <li><a href="?object=modules&action={{ module_data.dir }}">{{ module_data.lang|default(module_data.dir) }}</a></li>
                        {% endfor %}
                        <li><a href="?object=modules"><i class="fa fa-code-fork"></i> {{ language.admin_nav_more_link }}</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-book"></i> {{ language.admin_nav_components }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        {% for component_data in content.modmenu.components %}
                            <li><a href="?object=components&action={{ component_data.dir }}">{{ component_data.lang|default(component_data.dir) }}</a></li>
                        {% endfor %}
                        <li><a href="?object=components"><i class="fa fa-code-fork"></i> {{ language.admin_nav_more_link }}</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-puzzle-piece"></i> {{ language.admin_nav_hooks }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        {% for hook_data in content.modmenu.hooks %}
                            <li><a href="?object=hooks&action={{ hook_data.dir }}">{{ hook_data.lang|default(hook_data.dir) }}</a></li>
                        {% endfor %}
                        <li><a href="?object=hooks"><i class="fa fa-code-fork"></i> {{ language.admin_nav_more_link }}</a></li>
                    </ul>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right navbar-user">
                <li class="dropdown messages-dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bolt"></i> {{ language.admin_fastaccess_title }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li class="message-preview">
                            <a href="?object=components&action=news&make=add">
                                <span class="avatar"><i class="fa fa-plus fa-4x"></i></span>
                                <span class="name">{{ language.admin_fastaccess_addnews_title }}</span>
                                <span class="message">{{ language.admin_fastaccess_addnews_desc }}</span>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li class="message-preview">
                            <a href="?object=components&action=static&make=add">
                                <span class="avatar"><i class="fa fa-list-alt fa-4x"></i></span>
                                <span class="name">{{ language.admin_fastaccess_addpage_title }}</span>
                                <span class="message">{{ language.admin_fastaccess_addpage_desc }}</span>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li class="message-preview">
                            <a href="?object=components&action=feedback">
                                <span class="avatar"><i class="fa fa-envelope fa-4x"></i></span>
                                <span class="name">{{ language.admin_fastaccess_feedback_title }}</span>
                                <span class="message">{{ language.admin_fastaccess_feedback_desc }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li><a href="{{ system.script_url }}" target="_blank"><i class="fa fa-arrow-right"></i> {{ language.admin_open_site_link }}</a></li>
                <li><a href="{{ system.script_url }}/user/logout.html"><i class="fa fa-sign-out"></i> {{ language.admin_exit_link }}</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>

    <div id="page-wrapper">

        <div class="row">
            <div class="col-lg-12">
                {{ content.body }}
            </div>
        </div><!-- /.row -->

    </div><!-- /#page-wrapper -->
</div><!-- /#wrapper -->

</body>
</html>