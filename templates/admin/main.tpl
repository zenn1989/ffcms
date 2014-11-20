<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ language.admin_page_title }}</title>
    <script>
        var ffcms_host = '{{ system.script_url }}';
        var loader = '{{ system.loader }}';
    </script>

    <!-- Bootstrap core CSS -->
    <link href="{{ system.script_url }}/resource/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Add custom CSS here -->
    <link href="{{ system.theme }}/css/sb-admin.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ system.script_url }}/resource/fontawesome/4.2.0/css/font-awesome.min.css">

    <script src="{{ system.script_url }}/resource/jquery/1.11.1/jquery-1.11.1.min.js"></script>
    <script src="{{ system.script_url }}/resource/bootstrap/3.2.0/js/bootstrap.min.js"></script>

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
            <a class="navbar-brand" href="{{ system.script_url }}/{{ system.file_name }}"><i class="fa fa-globe"></i> FFCMS ADMIN - v{{ system.version }}</a>
        </div>
        <ul class="nav navbar-right top-nav">
            <li class="dropdown">

                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-bolt"></i> {{ language.admin_fastaccess_title }} <span class="badge">{{ content.head.feedback_day + content.head.comments_day }}</span> <b class="caret"></b>
                </a>
                <ul class="dropdown-menu message-dropdown">
                    <li class="message-preview">
                        <a href="?object=components&action=news&make=add">
                            <div class="media">
                                <span class="pull-left">
                                    <i class="fa fa-plus fa-5x"></i>
                                </span>
                                <div class="media-body">
                                    <h5 class="media-heading"><strong>{{ language.admin_fastaccess_addnews_title }}</strong></h5>
                                    <p>{{ language.admin_fastaccess_addnews_desc }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="message-preview">
                        <a href="?object=components&action=static&make=add">
                            <div class="media">
                                <span class="pull-left">
                                    <i class="fa fa-list-alt fa-5x"></i>
                                </span>
                                <div class="media-body">
                                    <h5 class="media-heading"><strong>{{ language.admin_fastaccess_addpage_title }}</strong></h5>
                                    <p>{{ language.admin_fastaccess_addpage_desc }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="message-preview">
                        <a href="?object=components&action=feedback">
                            <div class="media">
                                <span class="pull-left">
                                    <i class="fa fa-envelope fa-5x"></i>
                                </span>
                                <div class="media-body">
                                    <h5 class="media-heading"><strong>{{ language.admin_fastaccess_feedback_title }} <span class="badge alert-danger">{{ content.head.feedback_day }}</span></strong></h5>
                                    <p>{{ language.admin_fastaccess_feedback_desc }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="message-preview">
                        <a href="?object=modules&action=comments">
                            <div class="media">
                                <span class="pull-left">
                                    <i class="fa fa-comments fa-5x"></i>
                                </span>
                                <div class="media-body">
                                    <h5 class="media-heading"><strong>{{ language.admin_fastaccess_comments_title }} <span class="badge alert-danger">{{ content.head.comments_day }}</span></strong></h5>
                                    <p>{{ language.admin_fastaccess_comments_desc }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                </ul>
            </li>
            <li><a href="{{ system.url }}" target="_blank"><i class="fa fa-arrow-right"></i> {{ language.admin_open_site_link }}</a></li>
            <li><a href="{{ system.url }}/user/logout.html"><i class="fa fa-sign-out"></i> {{ language.admin_exit_link }}</a></li>
        </ul>
    </nav>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <ul class="nav navbar-nav side-nav admin-sidebar">
            <li{% if system.get_data.object == null or system.get_data.object == 'main' %} class="active"{% endif %}><a href="{{ system.script_url }}/{{ system.file_name }}"><i class="fa fa-home"></i> {{ language.admin_main_link }}</a></li>
            <li{% if system.get_data.object in ['settings', 'filemanager', 'antivirus', 'dump', 'updates'] %} class="active"{% endif %}>
                <a href="javascript:;" data-toggle="collapse" data-target="#system"><i class="fa fa-fire"></i> {{ language.admin_nav_system }} <i class="fa fa-fw fa-caret-down"></i></a>
                <ul class="collapse {% if system.get_data.object in ['settings', 'filemanager', 'antivirus', 'dump', 'updates'] %} in{% endif %}" id="system">
                    <li{% if system.get_data.object == 'settings' %} class="active"{% endif %}><a href="?object=settings"><i class="fa fa-cogs"></i> {{ language.admin_nav_li_settings }}</a></li>
                    <li{% if system.get_data.object == 'filemanager' %} class="active"{% endif %}><a href="?object=filemanager"><i class="fa fa-file-o"></i> {{ language.admin_nav_li_filemanager }}</a></li>
                    <li{% if system.get_data.object == 'antivirus' %} class="active"{% endif %}><a href="?object=antivirus"><i class="fa fa-shield"></i> {{ language.admin_nav_li_avir }}</a></li>
                    <li{% if system.get_data.object == 'dump' %} class="active"{% endif %}><a href="?object=dump"><i class="fa fa-floppy-o"></i> {{ language.admin_nav_li_backup }}</a></li>
                    <li{% if system.get_data.object == 'updates' %} class="active"{% endif %}><a href="?object=updates"><i class="fa fa-gavel"></i> {{ language.admin_nav_li_updates }}</a></li>
                </ul>
            </li>
            <li{% if system.get_data.object == 'modules' %} class="active"{% endif %}>
                <a href="javascript:;" data-toggle="collapse" data-target="#modules"><i class="fa fa-table"></i> {{ language.admin_nav_modules }} <i class="fa fa-fw fa-caret-down"></i></a>
                <ul class="collapse{% if system.get_data.object == 'modules' %} in{% endif %}" id="modules">
                    {% for module_data in content.modmenu.modules %}
                        <li{% if module_data.dir == system.get_data.action %} class="active"{% endif %}><a href="?object=modules&action={{ module_data.dir }}">{{ module_data.lang|default(module_data.dir) }}</a></li>
                    {% endfor %}
                    <li{% if system.get_data.object == 'modules' and system.get_data.action == null %} class="active"{% endif %}><a href="?object=modules"><i class="fa fa-code-fork"></i> {{ language.admin_nav_more_link }}</a></li>
                </ul>
            </li>
            <li{% if system.get_data.object == 'components' %} class="active"{% endif %}>
                <a href="javascript:;" data-toggle="collapse" data-target="#components"><i class="fa fa-book"></i> {{ language.admin_nav_components }} <i class="fa fa-fw fa-caret-down"></i></a>
                <ul class="collapse{% if system.get_data.object == 'components' %} in{% endif %}" id="components">
                    {% for component_data in content.modmenu.components %}
                        <li{% if component_data.dir == system.get_data.action %} class="active"{% endif %}><a href="?object=components&action={{ component_data.dir }}">{{ component_data.lang|default(component_data.dir) }}</a></li>
                    {% endfor %}
                    <li{% if system.get_data.object == 'components' and system.get_data.action == null %} class="active"{% endif %}><a href="?object=components"><i class="fa fa-code-fork"></i> {{ language.admin_nav_more_link }}</a></li>
                </ul>
            </li>
            <li{% if system.get_data.object == 'hooks' %} class="active"{% endif %}>
                <a  href="javascript:;" data-toggle="collapse" data-target="#hooks"><i class="fa fa-puzzle-piece"></i> {{ language.admin_nav_hooks }} <b class="caret"></b></a>
                <ul class="collapse{% if system.get_data.object == 'hooks' %} in{% endif %}" id="hooks">
                    {% for hook_data in content.modmenu.hooks %}
                        <li{% if hook_data.dir == system.get_data.action %} class="active"{% endif %}><a href="?object=hooks&action={{ hook_data.dir }}">{{ hook_data.lang|default(hook_data.dir) }}</a></li>
                    {% endfor %}
                    <li{% if system.get_data.object == 'hooks' and system.get_data.action == null %} class="active"{% endif %}><a href="?object=hooks"><i class="fa fa-code-fork"></i> {{ language.admin_nav_more_link }}</a></li>
                </ul>
            </li>
        </ul>


    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    {{ content.body }}
                </div>
            </div>

            <div class="row">
                <p class="text-center"><a href="#" onclick="window.history.back();return false;"><span class="label label-primary">{{ language.admin_goback }}</span></a></p>
            </div>
        </div>
    </div><!-- /#page-wrapper -->
</div><!-- /#wrapper -->
</body>
</html>